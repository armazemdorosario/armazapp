<?php

namespace Eventzapp;

use Facebook\FacebookAuthorizationException;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;
use Facebook\FacebookSession;
use Facebook\GraphUser;

class FacebookHelper {

	const DEFAULT_PICTURE_SIZE = 100;

	private $loginHelper;
	private $loginUrl;
	private $session;

	/**
	 * @link http://stackoverflow.com/questions/23413854/facebook-sdk-v4-for-php-minimal-example
	 */
	public function __construct($appId, $appSecret, $loginUrl, $appRequiredScope) {

		# Debugger::log('Setting default application...');
		FacebookSession::setDefaultApplication($appId, $appSecret);

		# Debugger::log('Setting login helper...');
		$this->loginHelper = new FacebookRedirectLoginHelper($loginUrl);

		try {
			# Debugger::log('Getting session from redirect...');
			$this->session = $this->loginHelper->getSessionFromRedirect();
		} catch(FacebookRequestException $ex) {
			Debugger::log('When Facebook returns an error. ' . $ex->getMessage());
		} catch(FacebookSDKException $ex) {
			switch ($ex->getCode()) {
				case 721:
					throw new Exception('Session not active, could not load state. Needs session_start();', 900);
					break;
				case 28:
					throw new Exception('Bad internet. Connection to Graph API timed out', 901);
					break;
				case 100:
					throw new Exception('Error validating verification code. If you are moving between development environments, please make sure your redirect_uri is identical to the one you used in the OAuth dialog request', 902);
					break;
				default:
					Debugger::log('While trying to get session from redirect: ' . $ex->getMessage() . $ex->getCode());
					break;
			} // end switch
		} catch(\Exception $ex) {
			switch ($ex->getCode()) {
				case 100:
					Debugger::log('This authorization code has been used.');
					break;
				
				default:
					Debugger::log('When validation fails or other local issues. ' . $ex->getMessage());
					break;
			} // end switch
		} // end try/catch

		if(isset($_SESSION['token'])) {
			$this->session = new FacebookSession($_SESSION['token']);
			try {
				$this->session->Validate($appId, $appSecret);
			}
			catch(FacebookSDKException $ex) {
				switch ($ex->getCode()) {
					case 35:
						Debugger::log('Unknown SSL protocol error in connection to graph.facebook.com:443');
						break;
					
					default:
						Debugger::log('On constructing Facebook helper class: ' . $ex->getMessage() . $ex->getCode());
						break;
				}
			}
			catch(FacebookAuthorizationException $ex) {
				$session = '';
			}
		}

		if(!is_null($this->loginHelper)) {
			#Debugger::log('Setting login URL...');
			if(is_array($appRequiredScope)) {
				$_appRequiredScope = $appRequiredScope[0];
			}
			else {
				$_appRequiredScope = $appRequiredScope;
			}
			$this->loginUrl = $this->loginHelper->getLoginUrl(array('scope'=>$_appRequiredScope));
		}

		if (isset($this->session)) {   
		    $_SESSION['token'] = $this->session->getToken();
		    $this->setCurrentUserProfile();
		} 

	}

	/**
	 * @link https://developers.facebook.com/docs/php/gettingstarted/4.0.0
	 */
	private function getLoginUrl() {
		return $this->loginUrl;
	}

	/**
	 * @link https://developers.facebook.com/docs/php/gettingstarted/4.0.0
	 */
	public function isUserLoggedIn() {
		return isset($this->session) && !is_null($this->session) && $this->session;
	}

	public function getLoginErrorCode() {
		return intval(filter_input(INPUT_GET, 'error_code'));
	}

	public function hadLoginError() {
		return 2 === $this->getLoginErrorCode() || 200 === $this->getLoginErrorCode() || 4201 === $this->getLoginErrorCode();
	}

	public function getParsedLoginUrl() {
		if(!method_exists($this, 'getLoginUrl')) {return null;}
		return parse_url($this->getLoginUrl());
	}

	public function getLoginUrlQueryArgs() {
		$return = array();
		if(!method_exists($this, 'getParsedLoginUrl')||!is_array($this->getParsedLoginUrl())) {return $return;}
		$args = explode('&', $this->getParsedLoginUrl()['query']);
		foreach($args as $arg) {
			$pair = explode('=', $arg);
			/* urldecode prevents "The redirect_uri URL is not properly formatted" error */
			$return[$pair[0]] = htmlentities(urldecode($pair[1]));
		}
		return $return;
	}

	public function isAppForMaintenance() {
		return 901 === $this->getLoginErrorCode() || 1349126 === $this->getLoginErrorCode();
	}

	/**
	 * @link https://developers.facebook.com/docs/php/howto/profilewithgraphapi/4.0.0
	 */
	public function setCurrentUserProfile() {
		if(!$this->isUserLoggedIn()) { return; }
		try {
			$this->currentUserProfile = (new FacebookRequest($this->session, 'GET', '/me'))->execute()->getGraphObject(GraphUser::className());
		}
		catch(FacebookRequestException $exception) {
			Debugger::log('While trying to set current user profile: ' . $e->getMessage() . $e->getCode());
		}
	}

	public function getCurrentUserProfile() {
		if(!$this->isUserLoggedIn()) { return; }
		return $this->currentUserProfile;
	}

	/**
	 * @todo Add sanitization on return
	 */
	public function getUserProfilePictureURL($userfbid, $size = FacebookHelper::DEFAULT_PICTURE_SIZE) {
		return 'https://graph.facebook.com/' . $userfbid . '/picture?redirect=1&width=' . intval($size) . '&height=' . intval($size);
	}

	public function getCurrentUserProfilePictureURL($size = FacebookHelper::DEFAULT_PICTURE_SIZE) {
		if(!$this->isUserLoggedIn()) { return; }
		return $this->getUserProfilePictureURL($this->getCurrentUserProfile()->getId());
	}

	/**
	 * Uses Facebook API to fetch info about event related to a benefit
	 *
	 * @param string $fbid Event ID
	 * @param string $fbid Desired key. If not set, will return the entire array.
	 * @return null|mixed Event info or a null result, if fails.
	 */
	public function getEventInfoViaFacebook($fbid, $key = '') {
		
		if(isset($fbid) && !empty($fbid) && !is_null($fbid) && in_array(strlen($fbid), array(15, 16))) {
			try {
				$request = new FacebookRequest($this->session, 'GET', '/' . $fbid);
				$response = $request->execute();
				$array = $response->getGraphObject()->asArray();
				if(empty($key) || !isset($array[$key])) {
					return $array;
				}
				else {
					return $array[$key];
				}
			} catch ( FacebookApiException $e ) {
				throw new Exception('Error when trying to get event info: ' . $e->getMessage(), 912);
			}
			catch ( FacebookAuthorizationException $e ) {
				throw new Exception('Facebook could not get event info because of an authorization exception or an invalid Event Facebook ID.', 913);
			}
		}
		else {
			throw new Exception('You tried to get event info via Facebook, but Event Facebook ID is invalid', 910);
		}
		
	}

	public function getPermissions() {
		try {
			$request = new FacebookRequest($this->session, 'GET', '/me/permissions');
			$response = $request->execute();
			$graphObject = $response->getGraphObject();
			return $graphObject->asArray();
		} catch ( FacebookApiException $e ) {
			Debugger::log( 'Error trying to check permissions: ' . $e->getMessage() );
			return null;
		}
	}

	/**
	 * Checks if user granted required permissions to app
	 * @param string $what Permission name
	 * @return boolean|null Whether if found the permission. Null if could not check.
	 */
	public function checkPermission($key) {

		foreach ($this->getPermissions() as $permission) {
			if(trim($key) === trim($permission->permission) && 'granted' === trim($permission->status)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Uses Facebook API to post something on timeline
	 * 
	 * @param	array			$data	Publication data
	 * @return	mixed|boolean	API call result or false if there was an error
	 */
	public function postOnTimeline($data) {
		try {
			$request = new FacebookRequest($this->session, 'POST', '/me/feed', $data);
			return $request->execute()->getGraphObject();
		}
		catch (FacebookRequestException $e) {
			Debugger::log('Request exception when trying to post on timeline, code ' . $e->getCode() . ' with message ' . $e->getMessage());
		}
		catch ( Exception $e ) {
			Debugger::log('Exception when trying to post on timeline, code ' . $e->getCode() . ' with message ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Uses Facebook API to confirm attend an user
	 * 
	 * @param string $eventfbid Event Facebook ID
	 * @return Ambigous <boolean, mixed> API call result or false if there was an error
	 */
	public function attendEvent($eventfbid) {
		try {
			$request = new FacebookRequest($this->session, 'POST', '/' . $eventfbid . '/attending');
			return $request->execute();
		}
		catch(FacebookPermissionException $exception) {
			throw new Exception('Error trying to attend user on event of ID ' . $eventfbid . '. Reason: ' . $exception->getMessage());
		}
		catch(FacebookApiException $exception) {
			throw new Exception('Error trying to attend user on event of ID ' . $eventfbid . '. Reason: ' . $exception->getMessage());
		}
	}

	/**
	 * Sends a notification to user using Facebook API
	 * 
	 * App must be running in HTTPs mode.
	 * In additiion, app must have canvas mode activated, with a secure and valid URL
	 * 
	 * @param array $data		Required. Array with data used by API to generate the notification.
	 * @param string $userfbid	ID of user that is going to be notified
	 * @return mixed			Facebook API response about notification
	 */
	public static function sendNotification(array $data, $userfbid) {
		if(!isset($userfbid) || !empty($userfbid)) {
			return false;
		}
		#$data['access_token'] = getenv('APP_ID') . '|' . getenv('APP_SECRET');
		try {
			$request = new FacebookRequest($this->session, 'POST', '/' . $userfbid . '/notifications', $data);
			$response = $request->execute();
			return $response->getGraphObject();
		}
		catch(FacebookPermissionException $exception) {
			throw new Exception('Permission exception when trying to notify user ' . $userfbid . '. Reason: ' . $exception->getMessage());
		}
		catch(FacebookApiException $exception) {
			throw new Exception('API Exception when trying to notify user ' . $userfbid . '. Reason: ' . $exception->getMessage());
		}
	}

}
