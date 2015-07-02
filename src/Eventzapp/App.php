<?php
namespace Eventzapp;

use \Benefit\Model\Benefit;
use \Benefit\Model\BenefitTable;
use \Japhpy\Db;
use \Mandrill;
use \Slim\Slim;
use \User\Model\User;
use \User\Model\UserTable;
use \UserBenefit\Model\UserBenefit;
use \UserBenefit\Model\UserBenefitTable;
use \UserStats\Model\UserStatsTable;
use \ViewUserBenefit\Model\ViewUserBenefitTable;

/**
 * This class has methods that throw exceptions from 80 to 88. Read the docs to learn more about them.
 */
class App {

	const VIPLIST_TYPE = 1;
	const SWEEPSTAKES_TYPE = 2;
	const TICKET_OBJECT = 1;

	private $config;

	/**
	 * Currently logged user info (fetched from Facebook API)
	 *
	 * @var object Call it using functions like getId() to get the current user ID
	 */
	private $currentUser;

	/**
	 * Currently logged user info (fetched from Database)
	 *
	 * Works only if user is signed up
	 *
	 * @var User Use properties like access_level or methods like isAdministrator()
	 */
	private $currentUserData;

	private $db;
	private $engine;
	private $facebook;
	private $mailer;
	private $messages = array();
	private $slim;

	/**
	* Constructor function
	*
	* Connects to Facebook API, application database and another classes to make this app work.
	*
	* @uses \Facebook
	* @uses armazemapp\PDOAdapter
	*/
	public function __construct($dir) {

		if(!App::isApacheRewriteEnabled()) {
			throw new Exception('Apache rewrite module must be enabled to this app work properly', 88);
		}

		try {
			$this->config = require $dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'application.php';
			$this->db = new Db($dir);
			$this->engine = new TemplateHelper(__DIR__);
			$this->facebook = new FacebookHelper(getenv('APP_ID'), getenv('APP_SECRET'), getenv('LOGIN_URL'), $this->config['required_scope']);
			$this->slim = new Slim();
			$this->slim->config('templates.path', getenv('TEMPLATE_DIR'));
			$this->slim->setName($this->config['app_meta']['title']);
			$this->engine->getEngine()->debugging = '1' === getenv('TEMPLATE_DEBUG');
			$this->engine->getEngine()->caching = '1' === getenv('TEMPLATE_CACHE');
			$this->engine->assign('env', getenv('ENV'));
		}
		catch(\Exception $exception) {
			switch($exception->getCode()) {
				case 1049:
					Debugger::log('Seems like you did not configure the database server: ' . $exception->getMessage());
					break;
				default:
					Debugger::log('While instantiating the app class: ' . $exception->getMessage() . $exception->getCode());
					break;
			} // end switch
		}
		catch(Facebook\FacebookSDKException $exception) {
			Debugger::log('While instantiating the app class: ' . $exception->getMessage() . $exception->getCode());
		}

		try {
			$this->setCurrentUserData();
		}
		catch(Exception $ex) {
			switch ($ex->getCode()) {
				case 820:
					throw new Exception('Could not set current user data. Graph API must be offline or current server must be not connected to Internet. This app cannot run.', 83);
					break;

				default:
					Debugger::log($ex->getMessage());
					break;
			}
		}

		$this->engine->assign('logged_in', $this->facebook->isUserLoggedIn());

		if( !$this->facebook->isUserLoggedIn() ) {
			$this->engine->assign('login_error', $this->facebook->hadLoginError());
			$this->engine->assign('login_query_args', $this->facebook->getLoginUrlQueryArgs());
			$this->engine->assign('login_url', $this->facebook->getParsedLoginUrl());
		}

		$this->engine->assign('html_schema', 'itemscope itemtype="http://schema.org/SoftwareApplication"');
		$this->engine->assign('html_meta', $this->config['app_meta']);
		$this->engine->assign('app_maintenance', $this->facebook->isAppForMaintenance());
		$this->engine->assign('app_name', $this->config['app_meta']['title']);
		$this->engine->assign('app_routes', $this->config['routes']);
		$this->engine->assign('body_classes', '');
		$this->engine->assign('user_patterns', User::$patterns);
		$this->engine->getEngine()->registerPlugin('function', 'benefit_classes', array($this, 'benefitClasses'));
		$this->engine->getEngine()->registerPlugin('function', 'profile_picture', array($this, 'profilePicture'));
		$this->engine->getEngine()->registerPlugin('function', 'post_data', array($this, 'postData'));
		$this->engine->getEngine()->registerPlugin('function', 'fb_event', array($this, 'getEventInfoViaFacebook'));
		$this->engine->getEngine()->registerPlugin('function', 'gender_can_attend', array($this, 'engineGenderCanAttend'));
		$this->engine->getEngine()->registerPlugin('function', 'g', array($this, 'g'));
		$this->engine->getEngine()->registerPlugin('function', 'level_css_class', array($this, 'getCssClassBasedOnLevel'));
		$this->handleMessages();
	}

	private function setCurrentUserData() {
		if(!is_object($this->facebook)) {
			throw new Exception('Could not instantiate Facebook API. Graph server must be offline or current server is not connected to Internet.', 820);
		}
		if(!method_exists($this->facebook, 'isUserLoggedIn')) {
			throw new Exception('Could not check if user is logged in. isUserLoggedIn() method missing.', 821);
		}
		if($this->facebook->isUserLoggedIn()) {
			// Please don't change this. Everything else on this app need this working perfectly.
			$this->currentUser = $this->facebook->getCurrentUserProfile();
		}
		if(is_null($this->currentUser) || !is_object($this->currentUser)) {return;}
		$this->engine->assign('current_user_id', $this->currentUser->getId());
		$this->engine->assign('current_user_name', $this->currentUser->getName());
		$this->engine->assign('current_user_email', $this->currentUser->getEmail());
		$this->engine->assign('current_user_firstname', $this->currentUser->getFirstName());
		$this->engine->assign('current_user_gender', $this->currentUser->getGender());
		$this->engine->assign('current_user_signed_up', $this->isUserSignedUp());

		if($this->isUserSignedUp()) {
			$userTable = new UserTable($this->db);
			$this->currentUserData = $userTable->fetch($this->currentUser->getId());
			$this->engine->assign('current_user_data', $this->currentUserData);
			$this->engine->assign('current_user_is_administrator', $this->currentUserData->isAdministrator());
		}

	}

	private static function isApache() {
		return function_exists('apache_get_modules');
	}

	private static function isApacheRewriteEnabled() {
		return App::isApache() && in_array('mod_rewrite', apache_get_modules());
	}

	public function getRoute($key) {
		$key = trim($key, ' /');
		return isset($this->config['routes'][$key]) ? $this->config['routes'][$key] : null;
	}

	public function addInfoToBenefit(Benefit $benefit) {

		$benefit->setIfCurrentGenderCanAttend($this->genderCanAttend($benefit->accepted_gender));

		if(!is_object($this->currentUser)) {
			throw new Exception('Cannot get current user info. Session must be corrupted.');
		}

		$userBenefitTable = new UserBenefitTable($this->db);
		$benefit->num_people_claimed = $userBenefitTable->fetchColumnCount('userfbid', "`eventfbid` = '{$benefit->eventfbid}' AND `benefit` = '{$benefit->benefit_type}' AND `benefit_object` = '{$benefit->object}'");
		$benefit->setIfCurrentUserAttended($userBenefitTable->exists($benefit->benefit_type, $this->currentUser->getId(), $benefit->eventfbid));
		$benefit->setInfoText($this->getViplistBenefitTextInfo($benefit));

		try {
			$facebookEventInfo = $this->facebook->getEventInfoViaFacebook($benefit->eventfbid);
			$benefit->exchangeFacebookArray($facebookEventInfo);
		}
		catch(\Facebook\FacebookSDKException $exception) {
			throw new Exception('Facebook SDK Exception when trying to fetch event info on Facebook. Check your server Internet connection', 86);
		}
	}

	public function handleMessages() {
		if(filter_input(INPUT_GET, 'vipListSuccess')) {
			$this->messages['success'] = 'É oficial: você tá na Lista VIP. Aproveite! :D.';
		}
		if(filter_input(INPUT_GET, 'vipListError')) {
			$this->messages['danger'] = 'Ih... não conseguimos adicionar você à Lista VIP.' . '<script>$zopim.livechat.say("Armazém, não consegui entrar na Lista VIP. Vocês podem me ajudar?");</script>';
		}
		if(filter_input(INPUT_GET, 'userSignedUp')) {
			$this->messages['success'] = 'Tudo certo com seu cadastro!';
		}
		if(filter_input(INPUT_GET, 'loginAgain')) {
			$this->messages['warning'] = 'Para garantir que o app vai funcionar direitinho, por favor... você pode fazer login de novo?';
		}
	}

	public function handleHome() {
		if(method_exists($this->facebook, 'hadLoginError') && $this->facebook->hadLoginError()) {
			$this->slim->redirect(getenv('APP_URL') . '/' . $this->getRoute('logout'));
		}
		$this->engine->assign('id_card_form_group_class', '');

		if($this->isUserSignedUp()) {

			$benefitTable = new BenefitTable($this->db);

			$allActiveVipLists = $benefitTable->fetchAllActiveVipLists();
			$numberOfActiveVipLists = count($allActiveVipLists);

			$userStatsTable = new UserStatsTable($this->db);
			$can_enter = $userStatsTable->canUserEnterOnVipLists($this->currentUser->getId());

			// Use Facebook API to bring additional info to every benefit
			try {
				foreach ($allActiveVipLists as $vipList) {
				$this->addInfoToBenefit($vipList);
				}
			}
			catch(Exception $exception) {
				throw new Exception('Error when trying to use Facebook API to bring additional info for active benefits', 87);
			}

			$this->engine->assign('all_active_vip_lists', $allActiveVipLists);
			$this->engine->assign('current_user_can_enter_vip_lists', $can_enter);
			$this->engine->assign('number_of_active_vip_lists', count($allActiveVipLists));
		}
	}

	public function handleSignup() {
		if(!class_exists('\User\Model\User')) { throw new Exception('Model class for User was not found', 84); }

		$user = new User();
		$data = filter_input_array(INPUT_POST);
		$data['fbid'] = $this->currentUser->getId();
		$data['fbname'] = $this->currentUser->getName();
		$data['fbgender'] = $this->currentUser->getGender();
		$data['access_level'] = 0;
		$user->exchangeArray($data);

		$userTable = new UserTable($this->db);
		$userSignedUp = false;

		try {
			$userSignedUp = $userTable->save($user);
		} catch (Exception $e) {
			switch ($e->getCode()) {
				case 508:
					# User ID card number already exists on database
					$this->messages['danger'] = '<strong>Temos um problema</strong>: Já existe uma pessoa cadastrada no aplicativo com o documento de identidade que você informou.<br />Pode ser que você já tenha se cadastrado com outra conta do Facebook. Por favor, envie inbox pra gente informando este problema.';
					$this->engine->assign('id_card_form_group_class', 'has-error');
					return;
					break;
				case 514:
					# User individual registration number already exists on database
					$this->messages['danger'] = '<strong>Temos um problema</strong>: Já existe uma pessoa cadastrada no aplicativo com o CPF que você informou.<br />Pode ser que você já tenha se cadastrado com outra conta do Facebook. Por favor, envie inbox pra gente informando este problema.';
					$this->engine->assign('ir_number_form_group_class', 'has-error');
					return;
					break;
			}
		}

		if($userSignedUp) {
			$this->slim->redirect(getenv('APP_URL') . '?userSignedUp=true');
		}
	}

	public function handleBenefitRules($eventfbid, $benefit_type) {
		$this->engine->assign('layout', 'benefit_rules.tpl');
		$benefitTable = new BenefitTable($this->db);
		$currentBenefitArgs = array($eventfbid, $benefit_type, App::OBJECT_TYPE);
		$currentBenefit = $benefitTable->fetch($currentBenefitArgs);
		$this->addInfoToBenefit($currentBenefit);

		$this->engine->assign('current_benefit', $currentBenefit);
	}

	public function handleVipListPost($eventfbid) {
		if(!$this->facebook->isUserLoggedIn()) {
			$this->slim->redirect(getenv('APP_URL') . '/' . $this->getRoute('logout'));
		}
		$userStatsTable = new UserStatsTable($this->db);
		if(!$userStatsTable->canUserEnterOnVipLists($this->currentUser->getId())) {
			throw new Exception('User cannot enter on VIP List because of some recent fail.', 851);
		}
		if(!class_exists('\UserBenefit\Model\UserBenefit')) {
			throw new Exception('Model class for UserBenefit was not found', 852);
		}

		$benefitTable = new BenefitTable($this->db);

		$data = filter_input_array(INPUT_POST);
		$vipList = $benefitTable->fetch($eventfbid);
		$vipListForSave = $benefitTable->fetch($eventfbid);
		try {
			$this->addInfoToBenefit($vipList);
		}
		catch(Exception $exception) {
			throw new Exception('Exception when trying to complement VIP List info with Facebook data. Internet connection must be slow.', 853);
		}

		$private = isset($data['private']) ? 'on' === $data['private'] : false;
		$messageOfEventName = isset($vipList->name) && !empty($vipList->name) ? 'do evento ' . $vipList->name : 'de um evento';

		$userBenefit = new UserBenefit();
		$userBenefit->exchangeArray(array(
			'userfbid' 	=> User::sanitizeAnyFbid($this->currentUser->getId()),
			'eventfbid' => $eventfbid,
			'private' 	=> true === $private ? '1' : '0',
			'benefit' 	=> App::VIPLIST_TYPE,
			'chosen' 	=> '0', // This is a VIP List. Chosen if for Sweepstakes ;)
			'actually_attended' => '1', // Let's assume that yes. But... it can be changed after...
			'chosen_by_fbid' => User::sanitizeAnyFbid($this->currentUser->getId()), // The user itself
			'benefit_object' => App::TICKET_OBJECT,
		));

		$userBenefitTable = new UserBenefitTable($this->db);
		try {
			$userWasAddedToList = $userBenefitTable->save($userBenefit);
		}
		catch(Exception $exception) {
			$this->slim->redirect(getenv('APP_URL') . '?vipListError=1');
			return;
		}

		$count = $userBenefitTable->fetchColumnCount('userfbid', "`eventfbid` = '{$eventfbid}' AND `benefit` = '" . App::VIPLIST_TYPE . "' AND `benefit_object` = '" . App::TICKET_OBJECT . "'");

		if(!isset($userWasAddedToList) || false === $userWasAddedToList) {
			$this->slim->redirect(getenv('APP_URL') . '?vipListError=notAdded');
			return;
		}

		$vipListForSave->num_people_claimed = strval($vipListForSave->num_people_claimed + 1);
		$vipListWasUpdated = $benefitTable->save($vipListForSave);

		if(!$vipListWasUpdated) {
			$this->slim->redirect(getenv('APP_URL') . '?vipListError=notUpdated');
			return;
		}

		/**
		 * If the user has authorized the application to perform the publish action
		 */
		$canPublishOnTimeline = $this->facebook->checkPermission('publish_actions');

		if($canPublishOnTimeline && false === $private) {
			$data = array(
				'link' => getenv('CANVAS_URL') . '/',
				'message' => 'Entrei para a Lista VIP ' . $messageOfEventName . ' usando o Armazapp',
				'picture' => getenv('APP_URL') . '/' . $this->config['app_meta']['image'],
				'name' => $this->config['app_meta']['title'],
				'description' => $this->config['app_meta']['description'],
				/* actions should be a JSON-encoded dictionary with "name" and "link" keys */
				'actions' => json_encode(array(
					'name' => 'Veja as regras',
					'link' => getenv('CANVAS_URL') . '/' . $this->getRoute('viplist') . '/' . $eventfbid . '/' . $this->getRoute('rules'),
				)),
			);
			$publishAction = $this->facebook->postOnTimeline($data);
		}

		/**
		 * @link https://developers.facebook.com/docs/facebook-login/permissions/v2.3#reference-rsvp_event
		 */
		$canAttendOnFacebook = $this->facebook->checkPermission('rsvp');

		if($canAttendOnFacebook && false === $private) {
			$this->facebook->attendEvent($eventfbid);
		}

		$notificationData = array(
			'template'	 => 'Você está participando da Lista VIP ' . $messageOfEventName,
			'href'		 => '?utm_source=facebook&utm_medium=notifications&utm_content=' . $eventfbid . '&utm_campaign=viplist&utm_term=' . $messageOfEventName,
		);
		$notificationResult = $this->facebook->sendNotification($notificationData, $this->currentUser->getId());

		$templateName = 'default';
		$templateContent = array(
			array( 'name' => 'message_title', 'content' => $this->currentUser->getName() . ', ' . $notificationData['template'] ),
			array( 'name' => 'message_content', 'content' => 'Você está participando desta Lista VIP. Não é preciso ir buscar seu ingresso: basta ir à portaria e informar seu nome, apresentando documento de identificação com foto.' ),
			// Não se esqueça de ler o <a href="' . getenv('CANVAS_URL') . '/regulamento.php?eventfbid=' . $eventfbid . '&benefit_type=" target="_blank">regulamento</a>.
		);

		$email = $this->currentUser->getEmail();

		if(!empty($email)) {
			$messageData = array(
				'subject' => $notificationData['template'],
				'text' => $templateContent[1]['content'],
				'from_email' => getenv('MAIL_FROM'),
				'from_name' => $this->config['app_meta']['title'],
				'to' => array(
					array(
						'email' => $this->currentUser->getEmail(),
						'name' => $this->currentUser->getName(),
						'type' => 'to',
					),
				),
				'headers' => array(
					'Reply-To' => 'contato@armazemdorosario.com.br'
				),
				'important' => false,
				'track_opens' => true,
				'track_clicks' => true,
				'inline_css' => true,
				'merge_language' => 'handlebars',
				'tags' => array('handleVipListPost'),
				'metadata' => array('website' => getenv('APP_URL')),
				'merge_vars' => array(
					array(
						'rcpt' => $this->currentUser->getEmail(),
						'vars' => array(
							array( 'name' => 'message_title', 'content' => $this->currentUser->getName() . ', ' . $notificationData['template'] ),
							array( 'name' => 'message_content', 'content' => 'Você está participando desta Lista VIP.' ),
							// Não se esqueça de ler o regulamento em ' . getenv('CANVAS_URL') . '/regulamento.php?eventfbid=' . $eventfbid . '&benefit_type=" target="_blank">regulamento</a>
						),
					),
				),
				'google_analytics_domains' => array('app.armazemdorosario.com.br'),
				'google_analytics_campaign' => 'viplist-email-notification',
			);
			try {
				if('production' === getenv('ENV')) {
					if(is_null($this->mailer)) {
						$this->mailer = new Mandrill(getenv('MAILER_API_KEY'));
					}
					$mailResult = $this->mailer->messages->sendTemplate($templateName, $templateContent, $messageData);
				}
			}
			catch(\Exception $exception) {
				Debugger::log($exception->getMessage());
			}
		}

		$this->slim->redirect(getenv('APP_URL') . '?vipListSuccess=1');

	}

	public function handleVipListGet($eventfbid) {
		$userBenefitViewClass = 'ViewUserBenefit\Model\ViewUserBenefitTable';
		if(!class_exists($userBenefitViewClass)) {
			throw new Exception('Class ViewUserBenefit was not loaded. Cannot show VIP List users.');
		}

		$userBenefitView = new ViewUserBenefitTable($this->db);
		if(!is_a($userBenefitView, $userBenefitViewClass)) {
			throw new Exception('Could not instantiate ViewUserBenefit.');
		}

		$users = $userBenefitView->fetchUsersByEvent($eventfbid, App::VIPLIST_TYPE, $this->currentUserData->isAdministrator());

		$this->engine->assign('current_viplist_users', $users);
		$this->engine->assign('layout', 'app_vip_list.tpl');
	}

	public function handleLogout() {
		session_destroy();
		$this->slim->redirect(getenv('APP_URL'));
	}

	public function run() {

		$this->engine->setTemplateFile('index.tpl');
		$this->engine->assign('layout', 'app_main_layout.tpl');

		$this->slim->notFound(function () {
		});

		$this->slim->get('/', array($this, 'handleHome'));
		$this->slim->post('/', array($this, 'handleHome'));
		$this->slim->get('/' . $this->getRoute('signup'), function() {
			$this->slim->redirect(getenv('APP_URL'));
		});
		$this->slim->get('/' . $this->getRoute('signup') . '/', function() {
			$this->slim->redirect(getenv('APP_URL'));
		});
		$this->slim->post('/' . $this->getRoute('signup'), array($this, 'handleSignup'));
		$this->slim->post('/' . $this->getRoute('signup') . '/', array($this, 'handleSignup'));
		$this->slim->post('/' . $this->getRoute('viplist'), function() {
			$this->slim->redirect(getenv('APP_URL'));
		});

		$this->slim->get('/' . $this->getRoute('logout'), array($this, 'handleLogout'));
		$this->slim->get('/' . $this->getRoute('logout') . '/', array($this, 'handleLogout'));
		$this->slim->get('/logout.php', array($this, 'handleLogout'));
		$this->slim->error(function (\Exception $e) {
		    die();
		});

		try {
			$this->slim->get('/' .  $this->getRoute('viplist') . '/:eventfbid', function($eventfbid) {
				$this->handleVipListGet($eventfbid);
			});
			$this->slim->get('/' .  $this->getRoute('viplist') . '/:eventfbid/', function($eventfbid) {
				$this->handleVipListGet($eventfbid);
			});
			$this->slim->post('/' . $this->getRoute('viplist') . '/:eventfbid', function($eventfbid) {
				$this->handleVipListPost($eventfbid);
			});
			$this->slim->post('/' . $this->getRoute('viplist') . '/:eventfbid/', function($eventfbid) {
				$this->handleVipListPost($eventfbid);
			});
			$this->slim->get('/' .  $this->getRoute('viplist') . '/:eventfbid/regulamento/', function($eventfbid) {
				$this->handleBenefitRules($eventfbid, App::VIPLIST_TYPE);
			});
		}
		catch(Exception $exception) {
			$this->slim->redirect(getenv('APP_URL') . $this->getRoute('logout'));
		}

		$this->slim->run();

		try {
			$this->engine->assign('app_messages', $this->messages);
			$this->engine->display();
		} catch (Exception $e) {
			Debugger::log('While running (generating the app output): ' . $e->getMessage());
		}
	}

	public function isUserSignedUp($userfbid = null) {
		if(!is_object($this->currentUser)) {
			// throw new Exception('Cannot check if user signed up: user has to login on Facebook', 801);
			return false;
		}
		if(!class_exists('\User\Model\User')) {
			throw new Exception('Model class for User was not found', 800);
		}
		$fbid = is_null($userfbid) ? User::sanitizeAnyFbid($this->currentUser->getId()) : User::sanitizeAnyFbid($userfbid);

		if(!User::isAnyFbidValid($fbid)) {
			throw new Exception('User Facebook ID is not valid', 802);
		}
		$userTable = new UserTable($this->db);

		try {
			$fetchedUser = $userTable->fetchBy('fbid', $fbid);
		}
		catch(\Exception $exception) {
			// Returns false because "could not find users"
			return false;
		}

		if(is_object($fetchedUser)) {
			$fbid = $fetchedUser->fbid;
			return User::isAnyFbidValid($fbid);
		}

		return false;

	}

	public function postData($params, $smarty) {
		if(!isset($params) || !isset($params['key']) || empty($params['key'])) {
			return '';
		}
		$filtered_input = filter_input(INPUT_POST, $params['key']);
		switch($params['key']) {
			case 'name':
				$filtered_input = User::sanitizeName($filtered_input);
				break;
			case 'id_card':
				$filtered_input = User::sanitizeAnyIdCard($filtered_input);
				break;
			case 'ir_number':
				$filtered_input = User::sanitizeAnyIrNumber($filtered_input);
				break;
		}
		return $filtered_input;
	}

	/**
	 * @todo sanitize params
	 */
	public function profilePicture($params, $smarty) {
		$id = isset($params['id']) ? User::sanitizeAnyFbid($params['id']) : null;
		$size = isset($params['size']) ? intval($params['size']) : 100;
		$src = null !== $id ? $this->facebook->getUserProfilePictureURL($id, $size) : $this->facebook->getCurrentUserProfilePictureURL($size);
		$name = isset($params['name']) ? $params['name'] : $this->currentUser->getName();
		$alt = sprintf(_('Photo of %s'), $name);
		$lazy = isset($params['lazy']);
		$lazySrcAttribute = $lazy ? 'data-original' : 'src';
		$class = $lazy ? 'lazy ' : '';
		$class .= isset($params['class']) ? $params['class'] : '';
		return '<img alt="'.htmlentities($alt).'" class="'.$class.'" '.$lazySrcAttribute.'="'.$src.'" height="'.$size.'" width="'.$size.'" />';
	}

	/**
	 * Test if a specific gender can attend a benefit
	 *
	 * @param	string	$benefitGender	Allowable genders for current benefit
	 * @param	unknown	$userGender		User gender
	 * @return	boolean					Whether the gender is allowed
	 */
	private function genderCanAttend($benefitGender, $userGender = null) {
		if(!is_object($this->currentUser)) {
			return null;
		}
		$userGender = is_null($userGender) ? $this->currentUser->getGender() : null;
		if(is_null($userGender)) {
			return false;
		}
		if(empty($benefitGender) || is_null($benefitGender)) {
			return true;
		}
		else {
			return $benefitGender === $userGender;
		}
	} // end function genderCanAttend

	public function engineGenderCanAttend($params, $smarty) {
		return $this->genderCanAttend($params['benefit_gender']);
	}

	public function getBenefitClasses($benefit) {
		$classes = array('strip');
		if(2===intval($benefit->status)) {
			$classes[] = 'strip-closed';
		}
		elseif($benefit->is_full_vip_list) {
			$classes[] = 'strip-full';
		}
		elseif(3===intval($benefit->status)) {
			$classes[] = 'strip-promoters';
		}
		elseif($this->genderCanAttend($benefit)) {
			$classes[] = 'strip-open';
		}
		else {
			// Do nothing
		}
		return implode(' ', $classes);
	}

	public function benefitClasses($params, $smarty) {
		return $this->getBenefitClasses($params['benefit']);
	}

	public function getEventInfoViaFacebook($params, $smarty) {
		if(!class_exists('\Benefit\Model\Benefit')) {
			Debugger::log('Model class for Benefit was not found');
			return null;
		}
		if(!Benefit::isAnyEventFbidValid($params['event'])) {
			return null;
		}
		try {
			$key = isset($params['key']) ? $params['key'] : '';
			return $this->facebook->getEventInfoViaFacebook($params['event'], $key);
		}
		catch(Exception $ex) {
			throw new Exception('App could not get event info via Facebook: ' . $ex->getMessage(), 81);
		}
	}

	public function g($params, $smarty) {
		if(!class_exists('\Eventzapp\Gender')) {
			return $params['general'];
		}
		return Gender::g($params['gender'], $params['general'], $params['females'], $params['males'], true);
	}

 /**
     * Creates a text with a VIP List info, according with the user relationship
     *
     * @see   Gender
     * @uses  Gender
     *
     * @param number    $num_people_claimed Number of people that attended the VIP List
     * @param boolean   $current_user_attended            Se o usuário está participando ou não da Lista VIP
     * @param boolean   $accepted_gender    Se o gênero do usuário atual é aceito na Lista VIP
     * @param string    $can_claim          Se o usuário pode reivindicar sua participação (sujeito à regra de ausências)
     */
    public function getViplistBenefitTextInfo(Benefit $benefit) {
        $text = '';
        switch ( $benefit->num_people_claimed ) {
            case 0:
                switch ($benefit->accepted_gender) {
                    case 'female':
                        $text = 'Nenhuma mulher';
                        break;
                    case 'male':
                        $text = 'Nenhum homem';
                    default:
                        $text = 'Ninguém';
                        break;
                }
                $text .= ' ' . _( 'entrou na lista ainda' );
                break;
            case 1:
                $text .= ($benefit->current_user_attended) ? _( 'You\'re the only person who is participating.' ) : _( 'Only one person is participating.' );
                break;
            default:
                $text .= '<strong>' . htmlentities( $benefit->num_people_claimed ) . '</strong> ';
                switch ($benefit->accepted_gender) {
                    case 'female':
                        $text .= 'mulheres';
                        break;
                    case 'male':
                        $text .= 'homens';
                        break;
                    default:
                        $text .= 'pessoas';
                        break;
                }
                $text .= ' ' . _( 'já entraram na lista' );
                $text .= ($benefit->current_user_attended) ? ', incluindo você' : '';
                break;
        } // end switch
        return $text;
    }

    /**
	 * Returns an specific CSS class according with $level
	 *
	 * @param	number	$level Required. Level number between 0 and 100.
	 * @return	string	'success', 'default', 'info', 'warning', 'danger' ou 'text-muted', dependendo do valor.
	 */
    public function getCssClassBasedOnLevel($level) {
    	if(is_array($level) && isset($level['level'])) {
    		$percent = abs($level['level']);
    	}
    	else {
    		$percent = $level;
    	}
    	$percent = number_format( $percent, 2 );
		if ( $percent >= 80 ) {
			return 'success';
		} elseif ( $percent >= 75 & $percent < 80 ) {
			return 'default';
		} elseif ( $percent >= 50 && $percent < 75 ) {
			return 'info';
		} elseif ( $percent >= 33.96 && $percent < 50 ) {
			return 'warning';
		} elseif ( $percent < 33.96 && $percent > 0 ) {
			return 'danger';
		} else {
			return 'text-muted';
		}
    }

}
