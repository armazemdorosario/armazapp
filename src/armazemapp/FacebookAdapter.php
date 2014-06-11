<?php

namespace armazemapp;
use \Exception;
use \DateTime;
use \Facebook;
use \FacebookApiException;
use armazemapp\PDOAdapter;

require_once 'vendor/facebook-php-sdk/src/facebook.php';

/**
 * Adapter para Facebook
 * 
 * Adapta a API do Facebook às funções específicas do aplicativo
 * 
 * @author Paulo H. (Jimmy) Andrade Mota C.
 * @todo Desacoplar funções
 *
 */
class FacebookAdapter
{
	
	/**
	 * Armazena as configurações do aplicativo, como ID do app e segredo
	 * @var array
	 */
	private static $config;
	
	/**
	 * Armazena os metadados OpenGraph, a serem gerados na saída HTML
	 * @var array
	 */
	private static $openGraphMeta;
	
	/**
	 * Armazena uma instância da API do Facebook
	 * @var \Facebook
	 * @see \Facebook
	 */
	private static $facebook;
	
	/**
	 * Armazena uma instância do banco de dados da aplicação
	 * @var PDOAdapter
	 * @see PDOAdapter
	 */
	private static $db;

	/**
	 * Armazena o ID do usuário logado atualmente
	 * @var string
	 */
	private static $user_id;

	/**
	 * Armazena dados do perfil do usuário no Facebook
	 * @var array
	 */
	private static $user_profile;

	/**
	 * Armazena a URL utilizada para login no aplicativo
	 * @var string
	 */
	private static $login_url;

	/**
	 * Armazena a URL utilizada para logout no aplicativo
	 * @var string
	 */
	private static $logout_url;

	/**
	 * Armazena as permissões que o usuário deu ao aplicativo
	 * @var array
	 */
	private static $permissions;

	/**
	 * Função construtora
	 * 
	 * Se conecta à API remota do Facebook e ao banco de dados local da aplicação. 
	 * @uses \Facebook
	 * @uses armazemapp\PDOAdapter
	 */
	public function __construct() {
		static::$config = require 'configs/FacebookAdapter.php';
		static::$openGraphMeta = require 'configs/OpenGraphMeta.php';
		static::$openGraphMeta['fb:admins'] = implode(', ', static::$config['admins']);
		static::$facebook = new Facebook(static::$config);
		static::$db = new PDOAdapter();
		static::$login_url = static::$facebook->getLoginUrl(static::$config['permission']);
		static::$logout_url = static::$facebook->getLogoutUrl();
	}

	/**
	 * Getter da classe
	 * 
	 * Retorna, de forma segura, a instância da API do Facebook, o ID do aplicativo, metadados e URLs
	 * 
	 * @param string $what
	 * @return Facebook|array|string
	 */
	public function __get($what) {
		switch($what) {
			case 'facebook':
				return self::$facebook;
			break;
			case 'appId':
				return static::$config['appId'];
			break;
			case 'logout_url':
				return self::$logout_url;
			break;
			case 'login_url':
				return self::$login_url;
			break;
			case 'openGraphMeta':
				return self::$openGraphMeta;
			break;
			default:
				return null;
			break;
		}
		return null;
	}

	/**
	 * Redireciona o usuário à página local de login
	 * 
	 * O redirecionamento só acontece se o usuário já não estiver na página de login
	 */
	public static function login() {
		if(!strstr($_SERVER['SCRIPT_NAME'], 'login')) {
			header('Location: login.php');
		}
	}

	/**
	 * Destrói a sessão local e desloga o usuário do Facebook
	 */
	public static function logout() {
		static::$facebook->destroySession();
		unset($_SESSION);
		header('Location: '.static::$logout_url);
	}

	/**
	 * Utiliza a API do Facebook para obter dados do usuário.
	 * 
	 * Se não encontrar dados, força o usuário a fazer login. 
	 * 
	 * @return multitype:|NULL As informações de perfil ou nulo quando o login não foi feito.
	 */
	public function getUser() {
		self::$user_id = self::$facebook->getUser();
		if(self::$user_id) {
			try {
				static::$user_profile = static::$facebook->api('/me', 'GET');
				return static::$user_profile;
			}
			catch(FacebookApiException $e) {
				error_log('Error trying to get user data: ' . $e->getType() . $e->getMessage());
				return self::login();
			}
		}
		else {
			self::login();
			return NULL;
		} // end if
		self::login();
	} // end if public function getUser

	/**
	 * Descobre o local (idioma) do usuário
	 * 
	 * @param string $separator Permite a troca de pt_BR para pt-br, por exemplo 
	 * @return string O locale do usuário
	 */	
	public function getUserLocale($separator = '_') {
		return str_replace('_', $separator, static::$user_profile['locale']);
	}

	/**
	 * Verifica se o usuário condeceu as devidas permissões para o funcionamento do app.
	 * @param string $what Nome da permissão
	 * @return boolean|null Verdadeiro ou falso se conseguiu verificar a permissão. Nulo se não.
	 */
	public static function checkPermission($what) {
		
		try {
			static::$permissions = static::$facebook->api('/me/permissions');
		}
		catch(FacebookApiException $e) {
			error_log('Error trying to check permissions: ' . $e->getMessage());
			return null;
		}
		
		$returnValue = true;
		$permissions_array = array();
		
		if(is_string($what)) {
			$permissions_array = explode(',', $what);
		}
		
		foreach($permissions_array as $permission) {
			if (!array_key_exists(trim($permission), static::$permissions['data'][0])) {
				$returnValue = false;
			}
		}
		
		return $returnValue;

	}

	/**
	 * Garante que uma permissão seja dada, redirecionando o usuário à tela de permissões
	 * @param string $what Nome da permissão a ser garantida
	 */
	public static function grantPermission($what) {
		if(!static::checkPermission($what)) {
			error_log('getLoginURL called on grant permission function for ' . $what);
			header('Location: '.static::$facebook->getLoginUrl(array('scope'=>$what)));
		}
	}

	/**
	 * Retorna a idade do usuário de acordo com o seu aniversário.
	 * 
	 * O Facebook deveria fazer isso por nós.
	 * 
	 * @todo: Desacoplar e inserir numa classe específica para Datas
	 * 
	 * @uses DateTime 
	 * @return int A idade do usuário
	 */
	public static function getUserAge() {
		static::grantPermission('user_birthday');
		$dateOfBirthday = date(static::$user_profile['birthday']);
		$dateOfBirthday = new DateTime($dateOfBirthday);
		$nowDate = new DateTime();
		$diff = $dateOfBirthday->diff($nowDate);
		return $diff->y;
	}

	/**
	 * Função-predicado que define se o usuário tem 18 anos ou mais 
	 * @return boolean Verdadeiro se o usuário é maior de idade. Falso se não é.
	 */
	public static function isUserOver18() {
		return static::getUserAge() >= 18;
	}

	/**
	 * Recupera uma string de um conjunto de strings de acordo com o gênero do usuário 
	 * 
	 * @param string $forFemales	String a ser retornada para mulheres
	 * @param string $forMales		String a ser retornada para homens
	 * @param string $forUndef		String a ser retornada para gênero indeterminado
	 * @param string $gender		String a ser testada
	 * @return string				String retornada de acordo com o teste
	 */
	public static function g($forFemales = 'a', $forMales = 'o', $forUndef = 'o(a)', $gender = null) { 
		$gender = is_null($gender) ? static::$user_profile['gender'] : $gender;
		if($gender=='female') {
			return $forFemales;
		}
		else if($gender=='male') {
			return $forMales;			
		}
		else {
			return $forUndef;
		}
	}	

	/**
	 * Busca, no banco de dados local, uma linha com informações sobre o usuário.
	 * 
	 * @param string $userfbid O ID do usuário. Se não for informado, é o ID do usuário atual. 
	 * @return Ambigous <string, PDOStatement> O resultado da consulta ou um erro. 
	 */
	public static function getUserInfo($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		return static::$db->query("SELECT * FROM `users` WHERE `fbid` = {$userfbid}");
	}

	/**
	 * Testa, no banco de dados local, se o usuário já está cadastrado
	 * 
	 * @param string $userfbid O ID do usuário. Se não for informado, é o ID do usuário atual.
	 * @return boolean Verdadeiro e o usuário está cadastrado. Falso se ainda não está.
	 */
	public static function userSignedUp($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		if(!static::userfbidIsValid()) { return false; }
		$signedUp = static::$db->rowCount("SELECT * FROM `users` WHERE `fbid` = {$userfbid}");
		return 1 == $signedUp;
	}

	/**
	 * Recupera qual é o nível de acesso do usuário.
	 * 
	 * @param string $userfbid O ID do usuário. Se não for informado, é o ID do usuário atual.
	 * @return boolean|number O nível de acesso do usuário, a partir de 0 (o mais baixo).
	 */
	public static function getUserLevel($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		$query = static::$db->query("SELECT `access_level` FROM `users` WHERE `fbid` = {$userfbid}");
		if(!$query) { return false; }
		return intval($query[0]['access_level']);
	}
	
	/**
	 * Utiliza a função de detecção de nível de acesso para verificar se o usuário é promoter (nível 1)
	 * 
	 * @return boolean Verdadeiro se o usuário é promoter.
	 */
	public static function userIsPromoter() {
		return static::getUserLevel() === 1;
	}
	
	/**
	 * Utiliza a função de detecção de nível de acesso para verificar se o usuário é promoter (nível 3)
	 * 
	 * @return boolean Verdadeiro se o usuário é administrador.
	 */
	public static function userIsAdministrator() {
		return static::getUserLevel() === 3;
	}

	/**
	 * Realiza o cadastro do usuário atual no banco de dados local
	 * 
	 * @param array $data Um vetor com dados coletados via formulário
	 * @return void|boolean|Ambigous <string, PDOStatement> Void se o usuário já está cadastrado. Falso se não foi possível cadastrar. O resultado da consulta, se foi possível.
	 */
	public static function signupUser($data) {
		if(static::userSignedUp()) { return null; }
		if(
			!isset($data['name']) || !isset($data['identidade']) || !isset($data['fbname']) || !isset($data['fbgender']) ||
			empty($data['name']) || empty($data['identidade']) || empty($data['fbname']) || empty($data['fbgender'])
		) {
			return false;
		}
		if(!static::userfbidIsValid($data['fbid'])) { return false; }
		$statement = "INSERT INTO `users` (`fbid`, `name`, `identidade`, `fbname`, `fbgender`) VALUES ('{$data['fbid']}', '{$data['name']}', '{$data['identidade']}', '{$data['fbname']}', '{$data['fbgender']}');";
		$query = static::$db->query($statement);
		if(!$query) {
			error_log('N&atilde;o foi poss&iacute;vel cadastrar o usu&aacute;rio. Verifique a consulta: ' . $statement);
			return false;
		}
		return $query;
	}

	/**
	 * Adiciona um evento no banco de dados
	 * 
	 * @deprecated
	 * @todo Corrigir a consulta para se adequar aos novos campos da tabela `events`
	 * @param unknown $data
	 * @return boolean|Ambigous <string, PDOStatement>
	 */
	public static function addEvent($data) {
		
		if(!isset($data['eventfbid']) || empty($data['eventfbid'])) {
			throw new Exception('Event FBID missing');
		}
		else {
			$data['eventfbid'] = trim($data['eventfbid']);
		}
		
		if(!isset($data['expiration_date']) || empty($data['expiration_date'])) {
			throw new Exception('Benefit expiration date missing');
		}
		else {
			$data['expiration_date'] = trim($data['expiration_date']);
		}
		
		if(!isset($data['max_num_people_chosen']) || empty($data['max_num_people_chosen'])) {
			throw new Exception('Benefit max num people missing');
		}
		else {
			$data['max_num_people_chosen'] = intval($data['max_num_people_chosen']);
		}
		
		$data['accepted_gender'] = !isset($data['accepted_gender']) && empty($data['accepted_gender']) ? '' : trim($data['accepted_gender']);
		switch ($data['accepted_gender']) {
			case 'female': case 'FEMALE': $data['accepted_gender'] = 'female'; break;
			case 'male': case 'MALE': $data['accepted_gender'] = 'male'; break;
			default: $data['accepted_gender'] = ''; break;
		}
		
		$data['benefit_type'] = !isset($data['benefit_type']) && empty($data['benefit_type']) ? 1 : intval($data['accepted_gender']);
		switch ($data['benefit_type']) {
			case 2: $data['benefit_type'] = 2; break;
			default: $data['benefit_type'] = 1; break;
		}
		
		$data['featured'] = !isset($data['featured']) && empty($data['featured']) ? 0 : intval($data['featured']);
		switch ($data['featured']) {
			case 1: $data['featured'] = 1; break;
			default: $data['featured'] = 0; break;
		}
		
		$data['status'] = !isset($data['status']) && empty($data['status']) ? 0 : intval($data['status']);
		switch ($data['status']) {
			case 0: case 1: case 2: case 3: $data['status'] = intval($data['status']); break;
			default: $data['status'] = 0; break;
		}
		
		$data['photo'] = trim($data['photo']);
		
		$statement = "INSERT INTO `benefits` (
		`accepted_gender`, `benefit_type`, `eventfbid`, `expiration_date`, `featured`,
		`max_num_people_chosen`, `num_people_chosen`, `num_people_claimed`, `object`,
		`photo`, `status`) VALUES (
		'{$data['accepted_gender']}', '{$data['benefit_type']}', '{$data['eventfbid']}',
		'{$data['expiration_date']}', '{$data['featured']}', '{$data['max_num_people_chosen']}', 
		'0', '0', '1', '{$data['photo']}', '{$data['status']}');";
		
		$stmt = static::$db->exec($statement);
		
		if(!$stmt) {
			error_log('N&atilde;o foi poss&iacute;vel cadastrar o evento. Verifique a consulta: ' . $statement);
			return false;
		}
		return $stmt;
	}

	/**
	 * Retorna quantos benefícios estão cadastrados no aplicativo.
	 * 
	 * @param number $benefit_type Torna possível o filtro por tipo de benefício (Lista VIP, Sorteio) 
	 * @param number $status Torna possível o filtro por status. Ao ser informado, exige $benefit_type
	 * @return number A quantidade de benefícios que atende aos filtros, se informados.
	 */
	public static function howManyEvents($benefit_type = null, $status = null) {
		$statement = "SELECT DISTINCT `eventfbid` FROM `benefits` WHERE ";
		$statement .= "`status` <> '0' AND ";
		if(!is_null($benefit_type) && intval($benefit_type) > 0) {
			$benefit_type = intval($benefit_type);
			$statement .= "`benefit_type` = '{$benefit_type}'";
		}
		else {
			$statement .= '1';
		} // end if
		return static::$db->rowCount($statement);
	}

	/**
	 * Retorna os ID's únicos de evento no Facebook cadastrados no banco de dados
	 * 
	 * @return Ambigous <string, PDOStatement> Os ID's ou um erro, caso a consulta não possa ser feita.
	 */
	public static function getCurrentEvents() {
		return static::$db->query("SELECT DISTINCT `eventfbid` FROM `benefits` WHERE `status` <> '0'");
	}	

	/**
	 * Retorna os benefícios atualmente cadastrados no banco de dados
	 * 
	 * @param number $benefit_type Torna possível o filtro por tipo de benefício (Lista VIP, Sorteio)
	 * @param number $status Torna possível o filtro por status. Ao ser informado, exige $benefit_type
	 * @return Ambigous <string, PDOStatement> Os benefícios que atendem aos filtros, se informados.
	 */
	public static function getCurrentBenefits($benefit_type = null, $status = null) {
		$statement = "SELECT * FROM `benefits` JOIN `benefits_object` ON `benefits_object`.`idobject` = `benefits`.`object` WHERE ";
		$statement .= "`status` <> '0'";

		if(!is_null($benefit_type) && intval($benefit_type) > 0) {
			$benefit_type = intval($benefit_type);
			$statement .= " AND `benefit_type` = '{$benefit_type}'";
		}
		
		if(!is_null($status)) {
			$status = intval($status);
			$statement .= " AND `status` = '{$status}'";
		}
		
		$statement .= ' ORDER BY `featured` DESC, `expiration_date` ASC';
		
		return static::$db->query($statement);
		
	} // end function getCurrentBenefits

	/**
	 * Busca, no banco de dados local, informações sobre o benefício
	 * @param string $eventfbid ID do evento desejado
	 * @param number $benefit_type Tipo de benefício. Por padrão, é 1 (Lista VIP).
	 * @return Ambigous <string, PDOStatement> Informações sobre o benefício ou um erro
	 */
	public static function getBenefitInfo($eventfbid, $benefit_type = 1) {
		$eventfbid = trim($eventfbid);
		return static::$db->query("SELECT * FROM `benefits` JOIN `benefits_object` ON (`benefits`.`object` = `benefits_object`.`idobject`) WHERE `eventfbid` = '{$eventfbid}' AND `benefit_type` = '{$benefit_type}'");
	}

	/**
	 * Busca, na API do Facebook, informações sobre o evento relacionado ao benefício
	 * @param string $fbid ID do evento desejado
	 * @return null|mixed As informações sobre o evento ou null, caso a consulta não dê certo.
	 */
	public static function getEventInfo($fbid) {
		if(!isset($fbid) || empty($fbid) || is_null($fbid)) {
			error_log ('You tried to get event info, but did not told what event is. Check getEventInfo function.');
			return null;
		}
		try {			
			return static::$facebook->api('/'.$fbid);
		}
		catch(FacebookApiException $e) {
			error_log('Error trying to get event ' . $fbid . ' info: ' . $e->getMessage());
			return null;
		}
	} // end function getEventInfo

	/**
	 * Obtêm uma lista de usuários escolhidos em um sorteio ou outro benefício
	 * 
	 * @param number $benefit_type O tipo de benefício. 1 = Lista VIP, 2 = Sorteio... 
	 * @param string $eventfbid O ID do evento
	 * @return Ambigous <string, PDOStatement> A lista de usuários ou um erro
	 */
	public static function getChosenUsersFor($benefit_type, $eventfbid) {
		return static::$db->query("SELECT * FROM `users_benefits` JOIN `users` ON (`users`.`fbid` = `users_benefits`.`userfbid`) WHERE `users_benefits`.`benefit` = '{$benefit_type}' AND `users_benefits`.`eventfbid` = '{$eventfbid}' AND `users_benefits`.`chosen` = 1 ORDER BY `users`.`fbname`");
	}

	/**
	 * Informa se o usuário participou de um benefício de um tipo específico
	 * 
	 * @param number $benefit_type	O tipo de benefício. 1 = Lista VIP, 2 = Sorteio...
	 * @param string $eventfbid		O ID do evento
	 * @param string $userfbid		O ID do usuário desejado. Se não informado, é o usuário atual.
	 * @return NULL|boolean			Verdadeiro
	 */
	public static function claimedBenefit($benefit_type, $eventfbid, $userfbid = null, $benefit_object = 1) {
		if(!isset($eventfbid) || empty($eventfbid) || is_null($eventfbid)) {
			error_log ('Cannot check if someone claimed benefit: Event FBID not informed. Check claimedBenefit fn');
			return NULL;	
		}
		if(!isset($benefit_type) || empty($benefit_type) || is_null($benefit_type) || intval($benefit_type) < 1) {
			error_log ('Cannot check if someone claimed benefit: Benefit type ID not informed. Check claimedBenefit fn');
			return NULL;
		}
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		if(is_array($userfbid)) {
			$userfbid = $userfbid['id'];
		}
		$statement = "SELECT * FROM `users_benefits` where `userfbid` = '{$userfbid}' AND `eventfbid` = '{$eventfbid}' AND `benefit` = '{$benefit_type}' AND `benefit_object` = '{$benefit_object}';";
		$rowCount = static::$db->rowCount($statement);
		return 1 == $rowCount;
	}

	/**
	 * Retorna se o usuário está participando de uma lista VIP.
	 * 
	 * @param string $eventfbid O ID do evento
	 * @param string $userfbid O ID do usuário. Obrigatório.
	 * @return null|Ambigous <null, boolean> Nulo se não foi possível saber. Verdadeiro se está. Falso se não está.
	 */
	public static function attendedToEvent($eventfbid, $userfbid) {
		if (!isset($eventfbid) || empty($eventfbid) || is_null($eventfbid)) {
			error_log ('You tried to find out if ' . $userfbid . ' attended to an event, but did not told what event is. Check attendedToEvent function.');
			return NULL;
		}
		
		$claimedBenefit = static::claimedBenefit(1, $eventfbid, $userfbid);
		return $claimedBenefit;
		
	} // end function attendedToEvent

	/**
	 * Utiliza a API do Facebook para confirmar a presença de um usuário em um evento.
	 * 
	 * @param string $eventfbid O ID do evento
	 * @return Ambigous <boolean, mixed> O resultado da ação na API ou um falso, se houve erro.
	 */
	public static function attendOnFacebook($eventfbid) {
		$apiResponse = null;
		try {
			$apiResponse = static::$facebook->api('/'.$eventfbid.'/attending', 'post');
		}
		catch (FacebookApiException $e) {
			$error_string = 'Erro ao tentar fazer o usuário ' . static::$user_id . ' confirmar presença no evento de ID ' . $eventfbid . ' no Facebook. Motivo: ' . $e->getMessage();
			error_log($error_string);
			mail('ti@ultracomunica.com.br', '[Armazapp] Erro ao confirmar presença via API do Facebook', $error_string);
			$apiResponse = false;
		}
		return $apiResponse;
	}

	/**
	 * Adiciona o usuário atual a um sorteio de um evento
	 * 
	 * @param string $eventfbid O ID do evento
	 * @return boolean Verdadeiro se foi possível adicionar o usuário. Falso se não.
	 */
	public static function competeToTicketSweepstakes($eventfbid) {
		$user = static::$user_profile;
		$userfbid = $user['id'];
		$benefit = 2;
		if(static::claimedBenefit(2, $eventfbid)) {
			error_log("O usu&aacute;rio {$user['name']} tentou participar do sorteio de ".$_POST['max_num_people_chosen'] > 1 ? $_POST['plural_name'] : "um" . $_POST['objectname']." para o evento {$eventfbid}, no qual j&aacute; est&aacute; participando.", E_USER_ERROR);
			return false;
		}
		
		static::$db->beginTransaction();
		static::$db->exec("INSERT INTO `users_benefits` (`userfbid`, `eventfbid`, `private`, `benefit`) VALUES ('{$userfbid}', '{$eventfbid}', '0', '{$benefit}')");
		static::$db->exec("UPDATE `benefits` SET `num_people_claimed` = `num_people_claimed` + 1 WHERE `benefits`.`eventfbid` = {$eventfbid} AND `benefits`.`benefit_type` = '{$benefit}' LIMIT 1;");
		static::$db->commit();
	}

	/**
	 * Insere um usuário na Lista VIP
	 *
	 * Faz algumas verificações obrigatórias e, caso tudo esteja tudo ok, insere a pessoa em alguma Lista VIP
	 *
	 * @param string $eventfbid       Um ID válido para o evento
	 * @param bool	 $private         Se a pessoa entrará na Lista em modo privado
	 * @param string $userfbid        Um ID válido para o usuário que está entrando na lista
	 * @param string $chosen_by_fbid  ID do usuário que está inserindo o usuário na lista. Se não informado, igual ao $userfbid
	 */
	public static function attendToEvent($eventfbid, $private = false, $userfbid = null, $chosen_by_fbid = null) {
		
		/**
		 * ID do usuário logado atualmente
		 *
		 * @var string Armazena o ID do usuário logado atualmente.
		 */
		$current_userfbid = static::$user_profile['id'];
		
		/**
		 * ID do usuário a ser inserido na lista
		 *
		 * @var string Armazena o ID do usuário a ser inserido na lista. Se não informado, será o usuário logado.
		 */
		$userfbid = is_null($userfbid) ? $current_userfbid : $userfbid;
		
		/**
		 * ID do usuário que está inserindo alguém na lista
		 *
		 * @var string Armazena o ID do usuário que está inserindo na lista. Se não informado, será o próprio usuário inserido.
		 */
		$chosen_by_fbid = is_null($chosen_by_fbid) ? $current_userfbid : $chosen_by_fbid;
		
		if ( !static::userfbidIsValid($current_userfbid) || !static::userfbidIsValid($userfbid) || !static::userfbidIsValid($chosen_by_fbid)) {
			echo '<div class="alert alert-danger">Erro ao tentar adicionar &agrave; lista. Algum dos IDs de usu&aacute;rio envolvidos s&atilde;o inv&aacute;lidos.</div>';
			return false;
		}
		
		if(!static::canClaimBenefits($userfbid)) {
			echo '<div class="alert alert-danger">Erro ao tentar adicionar &agrave; lista. Usu&aacute;rio deixou de comparecer recentemente a algum evento no qual ganhou cortesia.</div>';
			return false;
		}

		$users = static::getUserInfo($userfbid);
		$user = $users[0];

		$benefit = 1;

		$private = ($private==true) ? '1' : '0';
		$acceptedGenders = static::getEventAcceptedGenders($eventfbid);
		

		if(static::attendedToEvent($eventfbid, $userfbid)) {

			$error_message = "O usu&aacute;rio {$user['name']} tentou entrar na lista do evento {$eventfbid}, na qual j&aacute; est&aacute; participando.";
			echo '<div class="alert alert-danger">Erro ao tentar adicionar &agrave; lista. Usu&aacute;rio j&aacute; est&aacute; participando desta lista ou j&aacute; foi adicionado por algum <i>promoter</i>.</div>';
			error_log($error_message, E_USER_ERROR);

			return false;

		}

		else if(!static::genderCanAttend($acceptedGenders, $user['fbgender'])) {

			$error_message = "O usu&aacute;rio {$user['name']}, do g&ecirc;nero {$user['fbgender']}, tentou entrar na lista do evento {$eventfbid}, onde s&oacute; o g&ecirc;nero {$acceptedGenders} &eacute; aceito.";
			echo '<div class="alert alert-danger">N&atilde;o foi poss&iacute;vel adicionar &agrave; lista.';
			if($user['fbgender']=='male') {
				echo ' Voc&ecirc; colocou g&ecirc;nero masculino no Facebook. Esta lista s&oacute; aceita mulheres.</div>';
			}
			elseif($user['fbgender']=='female') {
				echo ' Voc&ecirc; colocou g&ecirc;nero feminino no Facebook. Esta lista s&oacute; aceita homens.</div>';
			}
			else {
				'</div>';
			}
			error_log($error_message, E_USER_ERROR);

			return false; 

		}
		else {
			
			static::$db->beginTransaction();
			$users_benefits_result =  static::$db->exec("INSERT INTO `users_benefits` (`userfbid`, `eventfbid`, `private`, `benefit`, `chosen_by_fbid`) VALUES ('{$userfbid}', '{$eventfbid}', '{$private}', '{$benefit}', '{$chosen_by_fbid}');");
			$benefits_sum_result = static::$db->exec("UPDATE `benefits` SET `num_people_claimed` = `num_people_claimed` + 1 WHERE `benefits`.`eventfbid` = {$eventfbid} AND `benefits`.`benefit_type` = '{$benefit}' LIMIT 1;");
			static::$db->commit();
			
			echo '<div class="alert alert-success">Parab&eacute;ns, '.$user['name'].' est&aacute; na lista!</div>';
		}

		return array($users_benefits_result, $benefits_sum_result);


	} // end function attendToEvent

	/**
	 * Informa qual gênero é aceito em um benefício de tipo específico
	 * 
	 * @param string $eventfbid O ID do evento. Obrigatório. 
	 * @param number $benefit_type O tipo de benefício. Por padrão, 1 = Lista VIP
	 * @return string 'male', 'female' ou um valor nulo, da forma como veio do banco de dados.
	 */
	public static function getEventAcceptedGenders($eventfbid, $benefit_type = 1) {
		$statement = "SELECT `accepted_gender` FROM `benefits` WHERE `eventfbid` = {$eventfbid} AND `benefit_type` = {$benefit_type};";
		$query = static::$db->query($statement);
		return $query[0]['accepted_gender'];
	} // end function getEventAcceptedGenders

	/**
	 * Obtêm a lista de todas as pessoas que estão participando de um benefício - mesmo as não escolhidas
	 * 
	 * @param string $eventfbid	O ID do evento. Obrigatório. 
	 * @param number $benefit	O tipo de benefício. Por padrão, 1 = Lista VIP
	 * @return null|Ambigous <string, PDOStatement> O resultado da consulta ou nulo, se houver erro.
	 */
	public static function getEventAttendees($eventfbid, $benefit = 1) {
		if($benefit == 2) {
			$statement = "SELECT DISTINCT * FROM `vw_users_promoters` WHERE `eventfbid` = {$eventfbid} AND `benefit` = {$benefit} ORDER BY `name`";		
		}
		else {
			$statement = "SELECT * FROM `vw_users_benefits` WHERE `eventfbid` = {$eventfbid} AND `benefit` = {$benefit} ORDER BY `name`";	
		}
		$query = static::$db->query($statement);
		if(is_object($query) || !is_array($query)) {
			error_log('Error trying to get event attendees. Query: ' . $statement);
			return null;
		}
		return $query;
	} // end function getEventAttendees

	/**
	 * Função-predicado que testa se um gênero específico pode participar de um benefício
	 * 
	 * @param	string	$eventAccepted	Gender Gênero aceitável pelo evento
	 * @param	unknown	$userGender		Gênero do usuário
	 * @return	boolean					Verdadeiro se o gênero é aceito. Falso se não.
	 */
	public static function genderCanAttend($eventAcceptedGender, $userGender) {
		if (empty($eventAcceptedGender) || is_null($eventAcceptedGender)) {
			return true;
		}
		else {
			return $userGender == $eventAcceptedGender;
		}
		return false;
	} // end function genderCanAttend

	/**
	 * Função-predicado que testa se o usuário atual é administrador do aplicativo
	 * 
	 * @todo Tornar consistente com a implementação de níveis de acesso
	 * @param	string	$fbid	O ID do usuário. Se não informado, será o ID do usuário atual.
	 * @return	boolean			Verdadeiro se o usuário é administrador. Falso se não.
	 */
	public static function userIsAdmin($fbid = null) {
		$fbid = is_null($fbid) ? static::$user_profile['id'] : $fbid;
		$admins = static::$config['admins'];
		return in_array($fbid, $admins);
	} // end function userIsAdmin	

	/**
	 * Utiliza a API do Facebook para postar na timeline do usuário
	 * 
	 * @param	array			$data	Dados para publicação
	 * @return	mixed|boolean			Resultado da chamada à API ou falso, se houve um erro
	 */
	public static function postOnTimeline($data) {
		try {
			return static::$facebook->api('me/feed', 'POST', $data);
		}
		catch(Exception $e){
			error_log('Error trying to post on timeline: ' . $e->getMessage());
			return false;
		}
	} // end function postOnTimeline

	/**
	 * Obtêm uma lista aleatória de usuários que estão participando de um benefício de tipo específico
	 * 
	 * @param	string	$eventfbid		O ID do evento. Obrigatório.
	 * @param	number	$limit			A quantidade de usuários a ser recuperada. Obrigatório.
	 * @param	number	$benefit_type	O tipo do benefício. Se não informado, é 2 = Sorteio.
	 * @return	Ambigous <string, PDOStatement> O resultado da consulta com a lista de usuários
	 */
	public static function getRandomUsers($eventfbid, $limit, $benefit_type = 2) {
		return static::$db->query("SELECT * FROM `vw_users_random` WHERE `eventfbid` = '{$eventfbid}' AND `benefit` = '{$benefit_type}' LIMIT 0,{$limit}");
	}

	/**
	 * Retorna se o usuário foi escolhido para um benefício. Geralmente é usado em sorteios.
	 * 
	 * @param	number	$benefit	Tipo de benefício. 1 = Lista VIP, 2 = Sorteio. Obrigatório.
	 * @param	string	$eventfbid	ID do evento. Obrigatório.
	 * @param	string	$userfbid	ID do usuário. Se não for informado, é o ID do usuário atual.
	 * @return	number				-1 se o usuário sequer concorreu. 0 se não foi escolhido. 1 se foi
	 */
	public static function isUserChosen($benefit, $eventfbid, $userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id']: $userfbid;
		$statement = "SELECT `chosen` FROM `users_benefits` WHERE `eventfbid` = '{$eventfbid}' AND `userfbid` = '{$userfbid}' AND `benefit` = '{$benefit}';";
		$query = static::$db->query($statement);
		if(empty($query)) {
			error_log("Erro ao verificar se o usuario $userfbid foi sorteado para um evento. Consulta ao banco de dados nao retornou resultados. Revise a consulta: $statement.", E_USER_ERROR);
			return null;
		}
		$rowCount = static::$db->rowCount($statement);
		return $rowCount ? $query[0]['chosen'] : -1;
	}

	/**
	 * Notifica os administradores do aplicativo por e-mail sobre alguém que entrou no pré-sorteio 
	 * 
	 * @see		Mail
	 * @param	number	$eventfbid	ID do evento
	 * @return	boolean				Verdadeiro se conseguiu notificar. Falso se não.
	 */
	public static function notifyAdminsAboutPredraw($eventfbid) {
		$eventinfo = static::getEventInfo($eventfbid);
		$eventname = $eventinfo['name'];
		$predraw_to = 'cristiano@armazemdorosario.com.br, divaldo@armazemdorosario.com.br, web@ultracomunica.com.br, ti@ultracomunica.com.br, webmaster@armazemdorosario.com.br';
		$predraw_subject = static::$user_profile['name'] . ' acabou de entrar na página de pré-sorteio do evento ' . $eventname;
		$predraw_message = '<p>Você pode acessar a área administrativa do aplicativo em https://app.armazemdorosario.com.br/admin.php e verificar se o sorteio foi concluído.</p><p>Se o sorteio demorar a ser fechado, entre em contato com ' . static::$user_profile['name'] . '.</p>';
		$predraw_email = new Mail($predraw_to, $predraw_subject, $predraw_message);
		return $predraw_email->send();
	}
	
	/**
	 * Envia a lista de ganhadores por e-mail aos administradores do aplicativo.
	 * 
	 * @param string $eventfbid		ID do evento. Obrigatório.
	 * @param number $benefit_type	Tipo de benefício. 1 = Lista VIP. 2 = Sorteio. Obrigatório
	 */
	public static function sendWinnersListToEmail($eventfbid, $benefit_type) {
		$eventinfo = static::getEventInfo($eventfbid);
		$event_related = $eventfbid;
		$eventname = $eventinfo['name'];
		if($benefit_type == '1') {
			$subject = 'A Lista VIP do evento ' . $eventname . ' acabou de ser fechada';
			$users = static::getEventAttendees($eventfbid, $benefit_type);
		}
		elseif($benefit_type == '2') {
			$subject = 'O sorteio do evento de ID ' . $eventname . ' acabou de ser realizado';
			$users = static::getChosenUsersFor($benefit_type, $eventfbid);
		}
		ob_start();
		include 'views/admin-list-winners.phtml';
		$message = ob_get_clean();
		$email = new Mail('cristiano@armazemdorosario.com.br, divaldo@armazemdorosario.com.br, ti@ultracomunica.com.br, web@ultracomunica.com.br, webmaster@armazemdorosario.com.br', $subject, $message);
		$email->send();
	}	
	
	/**
	 * Altera o status de um benefício.
	 * 
	 * Utilize alterar o estado de um benefício e até para definir uma Lista VIP para promoters
	 * 
	 * @param string $eventfbid		ID do evento. Obrigatório.
	 * @param number $benefit_type	Tipo de benefício. 1 = Lista VIP. 2 = Sorteio. Obrigatório
	 * @param number $status		Valor para o status. 0 = Despublicado. 1 = Aberto. 2 = Fechado. 3 = Promoters
	 * @return Ambigous <string, PDOStatement>
	 */
	public static function changeBenefitStatus($eventfbid, $benefit_type, $status) {
		$statement = "UPDATE `benefits` SET `status` = '{$status}' WHERE `benefits`.`eventfbid` = '{$eventfbid}' AND `benefits`.`benefit_type` = '{$benefit_type}';";
		return static::$db->query($statement);
	}

	/**
	 * Efetiva o sorteio de usuários
	 * 
	 * Lança os sorteados no banco de dados, avisa um por um no Facebook sobre o resultado
	 * e aciona o envio da lista de ganhadores por e-mail para os administradores 
	 * 
	 * @param string	$eventfbid ID do evento no Facebook 
	 * @param array		$userfbids Array com os IDs dos usuários sorteados
	 * @return void|multitype:unknown Ambigous <string, PDOStatement>
	 */
	public static function chooseUsers($eventfbid, $userfbids) {
		if(!is_array($userfbids) || !isset($eventfbid) || empty($eventfbid)) { return; }
		$users_chosen = count($userfbids);
		$userfbids_string = implode(', ', $userfbids);
		$chooseUsersQuery = static::$db->query("UPDATE `users_benefits` SET `chosen` = '1' WHERE `userfbid` IN ({$userfbids_string}) AND `eventfbid` = '{$eventfbid}' AND `benefit` = 2");
		$changeStatusQuery = static::$db->query("UPDATE `benefits` SET `status` = '2', `num_people_chosen` = '{$users_chosen}' WHERE `benefits`.`eventfbid` = '{$eventfbid}' AND `benefits`.`benefit_type` = 2;");
		foreach($userfbids as $userfbid) {
			static::notifyUserAboutSweepstakes($eventfbid, $userfbid);
		}
		static::sendWinnersListToEmail($eventfbid, 2);
		return array($chooseUsersQuery, $changeStatusQuery);
	}

	/**
	 * Notifica o usuário, via Facebook, sobre o resultado de um sorteio
	 * 
	 * @param string $eventfbid ID do evento no Facebook. Obrigatório.
	 * @param string $userfbid	ID do usuário a ser notificado. Obrigatório.
	 */
	public static function notifyUserAboutSweepstakes($eventfbid, $userfbid) {
		$event = static::getBenefitInfo($eventfbid, 1);
		$fbevent = static::getEventInfo($eventfbid);
		$eventname = isset($fbevent['name']) ? 'do evento "'.trim($fbevent['name']).'"' : 'de um evento';
		$notification_data = array(
				'template' => 'O sorteio dos ingressos ' .$eventname . ' foi finalizado. Confira o resultado!',
				'href' => '?utm_source=facebook&utm_medium=ticket_sweepstakes_notification',
		);
		static::sendNotification($notification_data, $userfbid);
	}

	/**
	 * Desfaz algum sorteio. Utilizar apenas em modo de teste.
	 * 
	 * @param string $eventfbid ID do evento a ter seu sorteio desfeito.
	 */
	public static function cleanUsers($eventfbid) {
		$chooseUsersQuery = static::$db->query("UPDATE `users_benefits` SET `chosen` = '0' WHERE `eventfbid` = '{$eventfbid}' AND `benefit` = 2");
		$changeStatusQuery = static::$db->query("UPDATE `benefits` SET `status` = '1' WHERE `benefits`.`eventfbid` = '{$eventfbid}' AND `benefits`.`benefit_type` = 2;");
	}

	/**
	 * Retorna uma lista de usuários que possua um nome igual ou parecido ao informado
	 * 
	 * @param	string	$query			O nome desejado.
	 * @param	bool	$use_soundex	Define se serão buscados nome foneticamente parecidos. Desativado por padrão.	
	 * @return Ambigous <string, PDOStatement> Um resultset com os nomes
	 */
	public static function searchUser($query, $use_soundex = false) {
		
		if($use_soundex) {
			$statement = "
SELECT *
FROM `vw_users_soundex`
WHERE (
	`fbid` NOT IN (
		SELECT `fbid`
		FROM `users`
		WHERE
			`name` LIKE '%{$query}%'
			OR `fbname` LIKE '%{$query}%'
			OR `fbid` = '{$query}'
	)
	AND (
		`name` LIKE '%{$query}%'
		OR `fbname` LIKE '%{$query}%'
		OR `fbid` = '{$query}'
		OR `soundex_name` LIKE '%" . soundex($query) ."%'
		OR `fbname_soundex` LIKE '%" . soundex($query) . "%'
		OR `fbid` = '$query'
	)
);";
			#var_dump($statement);
		}
		else {
			$query = str_replace('a', '_', $query);
			$query = str_replace('e', '_', $query);
			$query = str_replace('i', '_', $query);
			$query = str_replace('y', '_', $query);
			$query = str_replace(' ', '%', $query);
			$statement = "SELECT * FROM `users` WHERE `name` LIKE '%{$query}%' OR `fbname` LIKE '%{$query}%' OR `fbid` = '{$query}'";
		}
		return static::$db->query($statement);
	}

	/**
	 * Retorna uma lista de todos os benefícios nos quais um usuário participou
	 * 
	 * @param string $userfbid ID do usuário. Se não informado, será o ID do usuário atual.
	 * @return Ambigous <string, PDOStatement> Uma lista de benefícios.
	 */
	public static function getUserEvents($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id']: $userfbid;
		return static::$db->query("SELECT * FROM `vw_users_benefits` WHERE `userfbid` = {$userfbid} ORDER BY `timestamp` ASC");
	}

	/**
	 * Retira um benefício de um usuário. Deve ser utilizado apenas pelo administradores em modo de teste.
	 * 
	 * @todo Liberar acesso ao DELETE no banco de dados para evitar erro de permissão. Tirar var_dump
	 * 
	 * @param string	$eventfbid	ID do evento
	 * @param number	$userfbid	ID do usuário a ser removido do benefício
	 * @param number	$benefit	Tipo de benefício no qual o usuário deixará de participar	
	 * @return void|Ambigous <string, PDOStatement>
	 */
	public static function removeBenefitFromUser($eventfbid, $userfbid, $benefit) {
		if(!isset($userfbid) || !isset($eventfbid) || !isset($benefit) || empty($eventfbid)) { return; }
		$statement = "DELETE FROM `users_benefits` WHERE `userfbid` = '{$userfbid}' AND `benefit` = '{$benefit}' AND `eventfbid` = '{$eventfbid}' LIMIT 1";
		var_dump($statement);
		return static::$db->query($statement);
	}

	/**
	 * Remove o cadastro de usuário do aplicativo. Só deve ser feito quando o cadastro não é confiável.
	 *
	 * @todo Liberar acesso ao DELETE no banco de dados para evitar erro de permissão. Tirar var_dump
	 * 
	 * @param string $userfbid ID do usuário a ser removido. O usuário não pode ser administrador.
	 * @return boolean|Ambigous <string, PDOStatement>
	 */
	public static function removeUser($userfbid) {
		if(!static::userIsAdmin() || static::userIsAdmin($userfbid)) { return false; }
		$statement = "DELETE FROM `users` WHERE `users`.`fbid` = '{$userfbid}' LIMIT 1";
		var_dump($statement);
		return static::$db->query($statement);
	}

	/**
	 * Testa se o ID do usuário no Facebook é válido.
	 * 
	 * Para ser válido, deve ter 9, 10 ou 15 dígitos. Se tiver 10 ou 15, deve começar com 1.
	 * 
	 * @param string $userfbid
	 * @return boolean
	 */
	public static function userfbidIsValid($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id']: $userfbid;
		$userfbid = trim($userfbid);
		$length = intval(strlen($userfbid));
		$check_first_digit = substr($userfbid, 0, 1) === "1";
		switch($length) {
			case 9:
				return true;
			break;
			case 10:
			case 15:
				return $check_first_digit;
			break;
			default:
				return false;
			break;
		}
		return false;
	} // public static function userIsValid

	/**
	 * Envia uma notificação ao usuário utilizando a API do Facebook
	 * 
	 * Para que esta função funcione, o app precisa estar rodando em HTTP seguro.
	 * Além disso, o aplicativo precisa ter o modo de canvas ativado, com uma URL segura válida.
	 * 
	 * @param array $data		Um vetor com os dados utilizados pela API para gerar a notificação
	 * @param string $userfbid	O ID do usuário a ser notificado
	 * @return mixed			A resposta da API do Facebook sobre a notificação.
	 */
	public static function sendNotification($data, $userfbid = null) {
		$data['access_token'] = static::$config['appId'].'|'.static::$config['secret'];
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		return static::$facebook->api('/'.$userfbid.'/notifications', 'POST', $data);
	}

	/**
	 * Retorna uma lista de usuários inválidos.
	 * 
	 * Usuários inválidos são aqueles que possuem o nível de confiança menor que 33,96%.
	 * 
	 * @see FacebookAdapter::calculateUserTrustLevel
	 * 
	 * @return Ambigous <string, PDOStatement> A lista de usuários inválidos
	 */
	public static function getInvalidUsers() {
		return static::$db->query("SELECT * FROM `users` WHERE `fbname` = '' OR `fbgender` = '' OR `fbname` LIKE '%?%' OR `trust_level` < 33.96");
	}

	/**
	 * Retorna a quantidade de usuários inválidos.
	 * 
	 * @todo	Otimizar esta função para que, quando utilizada em conjunto com getInvalidUsers(), não realizar duas consultas.
	 * @todo	Retirar a função desta classe
	 * @return	number A quantidade de usuários inválidos.
	 */
	public static function getInvalidUsersNumber() {
		return static::$db->rowCount("SELECT * FROM `users` WHERE `fbname` = '' OR `fbgender` = '' OR `fbname` LIKE '%?%' OR `trust_level` < 33.96");
	}

	/**
	 * Retorna a lista de usuários que se registraram ou tiveram seu cadastro atualizado hoje.
	 * 
	 * @todo	Otimizar esta função para que, quando utilizada em conjunto com getInvalidUsersNumber(), não realizar duas consultas.
	 * @todo	Retirar a função desta classe.
	 * @return	Ambigous <string, PDOStatement> A lista de usuários
	 */
	public static function getUsersThatRegisteredToday() {
		return static::$db->query('SELECT * FROM `users` WHERE `timestamp` >= CURDATE() ORDER BY `timestamp` DESC LIMIT 0, 10');
	}

	/**
	 * Confirma a presença real de um usuário em algum evento relacionado a um benefício específico.
	 * 
	 * @param	string	$userfbid			O ID do usuário a ser lançada a presença/ausência. Obrigatório.
	 * @param	string	$eventfbid			O ID do evento relacionado ao lançamento de presença/ausência. Obrigatório. 
	 * @param	number	$benefit			O tipo de benefício relacionado ao lançamento de presença/ausência. Obrigatório.
	 * @param	number	$actually_attended	0 se o usuário não compareceu ao evento no dia. 1 se compareceu.
	 * @return	Ambigous <string, PDOStatement> Resultado da consulta de alteração
	 */
	public static function attendUserToBenefit($userfbid, $eventfbid, $benefit, $actually_attended) {
		$userfbid = filter_var($userfbid, FILTER_SANITIZE_NUMBER_INT);
		$eventfbid = filter_var($eventfbid, FILTER_SANITIZE_NUMBER_INT);
		$benefit = filter_var($benefit, FILTER_SANITIZE_NUMBER_INT);
		$actually_attended = filter_var($actually_attended, FILTER_SANITIZE_NUMBER_INT);
		$statement = "UPDATE `users_benefits` SET `actually_attended` = '$actually_attended' WHERE `userfbid` = '$userfbid' AND `eventfbid` = '$eventfbid' AND `benefit` = '$benefit';";
		$query = static::$db->query($statement);
		return $query;
	}

	/**
	 * Calcula o nível de confiança do cadastro do usuário
	 * 
	 * Este cálculo é feito através da comparação do nome cadastrado no banco de dados local com o nome no Facebook
	 * 
	 * @param string $name		Nome cadastrado no banco de dados local. Obrigatório.
	 * @param string $fbname	Nome cadastrado no Facebook. Obrigatório.
	 * @return number			Um valor entre 0 e 100.  
	 */
	public static function calculateUserTrustLevel($name, $fbname) {
		if(empty($name) || empty($fbname)) {
			return 0;
		}
		similar_text(static::formatUserName($name), static::formatUserName($fbname), $percent);
		return number_format($percent, 2);
	}

	/**
	 * Define o nível de confiança do usuário.
	 * 
	 * Além de comparar os nomes, testa se o usuário é administrador e se o cadastro é realmente de uma pessoa.
	 * 
	 * @todo   Retirar esta função desta classe
	 * @param  string	$userfbid	ID do usuário a ser analisado.
	 * @return number				Número entre 0 e 100.
	 */
	public static function setUserTrustLevel($userfbid) {
		$userfbid = filter_var($userfbid, FILTER_SANITIZE_NUMBER_INT);
		$user = static::getUserInfo($userfbid);
		$trust_level = static::calculateUserTrustLevel($user[0]['fbname'], $user[0]['name']);
		if(static::userIsAdmin($userfbid)) { $trust_level = 100; }
		$words = str_word_count($user[0]['name'], 2);
		if(count($words) < 2) {
			$trust_level = $trust_level = 0.01;
		}
		foreach ($words as $word) {
			if ($word == 'Rep' || $word == 'República' || $word == 'BRS') {
				$trust_level = 0.01;
			}
		}
		$trust_level = number_format($trust_level, 2);
		$statement = "UPDATE `users` SET `trust_level` = '$trust_level' WHERE `fbid` = '$userfbid' LIMIT 1;";
		static::$db->query($statement);
		return $trust_level;
	}

	/**
	 * Função-predicado que define se o cadastro do usuário é falso
	 * 
	 * @param  number	$trust_level	Nível de confiança do usuário atual.
	 * @return boolean					Verdadeiro se o usuário parece ter um cadastro falso.
	 */
	public static function userAppearsToBeFake($trust_level) {
		$trust_level = number_format($trust_level, 2);
		return ($trust_level < 33.95 && $trust_level >= 0);
	}

	/**
	 * Retorna uma classe CSS específica, de acordo com o nível de confiança do usuário.
	 * 
	 * @todo	Retirar função desta classe e colocar num Helper para View
	 * @param	number	$trust_level Nível de confiança
	 * @return	string	'success', 'default', 'info', 'warning', 'danger' ou 'text-muted', dependendo do valor.
	 */
	public static function getTrustLevelCssClass($trust_level) {
		$percent = number_format($trust_level, 2);
		if ($percent >= 80 && $percent <= 100) {
			return 'success';
		}
		elseif ($percent >= 75 & $percent < 80) {
			return 'default';
		}
		elseif ($percent >= 50 && $percent < 75) {
			return 'info';
		}
		elseif($percent >= 33.96 && $percent < 50) {
			return 'warning';
		}
		elseif( $percent < 33.96 && $percent > 0 ) {
			return 'danger';
		}
		else {
			return 'text-muted';
		}
	}

	/**
	 * Obtêm uma frase explicando o motivo do usuário ter sido classificado com tal nível
	 * 
	 * @todo	Retirar desta classe.
	 * @param	number $trust_level Nível de confiança (entre 0 e 100). Obrigatório.
	 * @return	string Frase com a explicação
	 */
	public static function getTrustLevelExplanation($trust_level) {
		$percent = number_format($trust_level, 2);
		if ($percent >= 80 && $percent <= 100) {
			return 'Nome exato';
		}
		elseif ($percent >= 75 & $percent < 80) {
			return 'Supresss&atilde;o de nome ou sobrenome';
		}
		elseif ($percent >= 50 && $percent < 75) {
			return 'Supress&atilde;o de nome e/ou uso de apelido';
		}
		elseif($percent >= 33.96 && $percent < 50) {
			return 'Uso de apelido e adi&ccedil;&atilde;o de sobrenome';
		}
		elseif($percent >= 0.01 && $percent < 33.96 ) {
			return 'Nome do perfil muito diferente do nome do cadastro';
		}
		else {
			return 'N&iacute;vel de confian&ccedil;a ainda n&atilde;o calculado';
		}
	}

	/**
	 * Sanitiza o nome do usuário, retirando acentos e corrigindo capitalização.
	 * 
	 * @todo Retirar desta classe e torná-lo compatível com a função nativa de sanitização do PHP 
	 * @param string $name Nome
	 * @return string Nome formatado
	 */
	public static function formatUserName($name) {
		$from = array('.', 'Á', 'á', 'Ã', 'ã', 'â', 'ç', 'é', 'ê', 'Í', 'í', 'ñ', 'õ', 'ú', 'Ú');
		$to   = array(' ', 'A', 'a', 'A', 'a', 'a', 'c', 'e', 'e', 'I', 'i', 'n', 'o', 'u', 'U');
		$name = str_replace($from, $to, $name);
		$words = str_word_count($name, 1);
		$formatted_name = array();
		foreach($words as $word) {
			$word = strtolower($word);
			switch ($word) {
				case 'da':
				case 'das':
				case 'de':
				case 'do':
				case 'dos':
				case 'e':
					$formatted_name[] = $word;
				break;
				case 'se':
					$formatted_name[] = 'de';
				break;
				default:
					$formatted_name[] = ucwords($word);
				break;
			}
		}
		return implode(' ', $formatted_name);
	}

	/**
	 * Retorna as últimas ausências de um usuário nos eventos
	 * 
	 * @param string $userfbid ID do usuário. Se não informado, será o ID do usuário atual.
	 * @return null|number Nulo se o ID de usuário não for válido. Número de 0 a N se for válido. 
	 */
	public static function getUserRecentFails($userfbid) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		if(!static::userfbidIsValid($userfbid)) {
			return null;
		}
		else {
			$query = static::$db->query("SELECT COUNT(`actually_attended`) as sum FROM `vw_users_stats` WHERE `fbid` = '$userfbid' AND `actually_attended` = '0';");
			return $query[0]['sum'];
		}
	}
	
	/**
	 * Função-predicado que responde se um usuário tem direito a participar dos próximos benefícios
	 * 
	 * Ela é calculada de acordo com as últimas ausências de um usuário nos eventos.
	 * As regras específicas de ausência devem ser implementadas no banco de dados.
	 * 
	 * @param string $userfbid
	 * @param string $user_recent_fails
	 * @return boolean
	 */
	public static function canClaimBenefits($userfbid = null, &$user_recent_fails = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		if(!static::userfbidIsValid($userfbid)) {
			return false;
		}
		else {
			$user_recent_fails = intval(static::getUserRecentFails($userfbid));
			return $user_recent_fails === 0;
		}
	}

	/**
	 * Obtém um resumo da participação do usuário no aplicativo.
	 * 
	 * Este resumo inclui quantidade de vezes que participou de Listas VIP e Sorteios.
	 * Nos sorteios, as vezes em que foi sorteado. Informa também as vezes em que se ausentou.
	 * 
	 * @param string $userfbid ID do usuário. Se não informado, será o ID do usuário atual.
	 * @return NULL|Ambigous <string, PDOStatement>
	 */
	public static function getUserStats($userfbid) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		if(!static::userfbidIsValid($userfbid)) {
			return null;
		}
		else {
			return static::$db->query("SELECT * FROM `vw_users_stats` WHERE `fbid` = '$userfbid';");
		}
	}

	/**
	 * Escreve um texto com informações sobre uma Lista VIP, de acordo com a sua relação com o usuário.
	 * 
	 * @see	  Gender
	 * @uses  Gender
	 * 
	 * @todo  Tornar a função mais inteligente, permitindo que ela "puxe" algumas informações do benefício no banco de dados.
	 * @todo  Retirar a função desta classe. Mover para uma classe relacionada a linguagem.
	 *  
	 * @param number	$num_people_claimed	Número de pessoas que estão participando da Lista VIP
	 * @param boolean	$claimed			Se o usuário está participando ou não da Lista VIP
	 * @param boolean	$accepted_gender	Se o gênero do usuário atual é aceito na Lista VIP
	 * @param string	$can_claim			Se o usuário pode reivindicar sua participação (sujeito à regra de ausências)
	 */
	public static function getViplistBenefitTextInfo($num_people_claimed, $claimed, $accepted_gender, $can_claim = true) {
		switch($num_people_claimed) {
			case 0:
				Gender::g($accepted_gender, 'Ningu&eacute;m', 'Nenhuma mulher', 'Nenhum homem');
				echo ' ' . _('entrou na lista ainda');
			break;
			case 1:
				echo ($claimed) ? _('You\'re the only person who is participating.') : _('Only one person is participating.');
			break;
			default:
				echo '<strong>' . htmlentities($num_people_claimed) . '</strong>&nbsp;';
				Gender::g($accepted_gender, 'pessoas', 'mulheres', 'homens'); 
				echo ' ' . _('j&aacute; entraram na lista');
				echo ($claimed) ? ', '. _('including you') : '';
			break;
		} // end switch
		#echo (!$claimed && $can_claim) ? '<br />' . _('What are you waiting for join in too?') : '';
	} // end public static function getViplistBenefitTextInfo

	/**
	 * Escreve um texto com informações sobre um sorteio, de acordo com a sua relação com o usuário.
	 * 
	 * @todo  Tornar a função mais inteligente, permitindo que ela "puxe" as informações do benefício no banco de dados.
	 * @todo  Retirar a função desta classe. Mover para uma classe relacionada a linguagem.
	 * 
	 * @param number	$num_people_claimed	Número de pessoas que estão concorrendo
	 * @param boolean	$claimed			Se o usuário está concorrendo ou não ao sorteio
	 * @param string	$can_claim			Se o usuário pode reivindicar sua participação (sujeito à regra de ausências)
	 */
	public static function getSweepstakesBenefitTextInfo($num_people_claimed, $claimed, $can_claim = true) {
		switch($num_people_claimed) {
			case 0:
				echo _('No one is participating yet.');
			break;
			case 1:
				echo ($claimed) ? _('You\'re the only person who is participating.') : _('Only one person is participating.');
			break;			
			default:
				echo '<strong>' . htmlentities($num_people_claimed) . '</strong>&nbsp;';
				echo _('people are participating');
				echo ($claimed) ? ', '. _('including you') : '';
			break;
		} // end switch
		#echo (!$claimed && $can_claim) ? '<br />' . _('What are you waiting for join in too?') : '';
	} // end public static function getSweepstakesBenefitTextInfo

	/**
	 * Retorna um código HTML para imagem de perfil de usuário ou evento.
	 * 
	 * @param unknown $userfbid ID do usuário. Obrigatório.
	 * @param unknown $userfbname Nome do usuário no Facebook. Obrigatório.
	 * @param number $size Tamanho da imagem. Padrão: 100. Em telas Retina, é a metade do valor informado.
	 * @param string $cssClass Classe CSS personalizada a ser aplicada.
	 */
	public static function getUserPicture($userfbid, $userfbname, $size = 100, $cssClass = '') {

?>
<img
	alt="<?php echo _('Photo of'); ?> <?php echo $userfbname; ?>"
	class="img-circle img-responsive lazy <?php echo $cssClass; ?>"
	data-original="//graph.facebook.com/<?php echo($userfbid); ?>/picture?redirect=1&amp;width=<?php echo($size); ?>&amp;height=<?php echo($size); ?>"
	<?php echo 'srcset="//graph.facebook.com/' . $userfbid . '/picture?width=' . $size*2 . '&height=' . $size*2 . ' 2x"'; ?>
	width="<?php echo($size); ?>"
	height="<?php echo($size); ?>" 
	style="min-width: 16px; min-height: 16px; max-width: <?php echo($size*2); ?>; max-height: <?php echo($size*2); ?>;"
/>
<?php
	}

	/**
	 * Função-predicado que detecta se o aplicativo está funcionando em modo Canvas
	 * 
	 * @todo	Finalizar função
	 * @return	boolean Verdadeiro se o aplicativo está funcionando em modo Canvas.
	 */
	public static function isCanvas(){
		if(!isset($_REQUEST['signed_request'])) { return false; }
		$signed_request = static::$facebook->getSignedRequest();
		return isset($signed_request);
	}

	/**
	 * Obtém a lista de promoters
	 * 
	 * @return Ambigous <string, PDOStatement> Lista de promoters
	 */
	public static function getPromoters() {
		return static::$db->query('SELECT * FROM `users` WHERE `access_level` = 1 ORDER BY `fbname`;');
	}
	
	/**
	 * Obtém estatísticas dos promoters relacionadas a um evento
	 * 
	 * @todo	Retirar método desta classe
	 * @param	string $eventfbid ID do evento.
	 * @return	Ambigous <string, PDOStatement> Listagem dos promoters e suas estatísticas.
	 */
	public static function getPromotersStats($eventfbid = null, $divide_by_gender = false) {

		if($divide_by_gender) {
			$statement = "SELECT * FROM `vw_promoters_gender_stats`";
		}
		else {
			$statement = "SELECT * FROM `vw_promoters_stats`";
		}

		if( !is_null( $eventfbid ) ) {
			$eventfbid = trim($eventfbid);
			$statement .= " WHERE `eventfbid` = '$eventfbid'";
		}
		$statement .= ";";
		return static::$db->query($statement);
	}
	
	public static function getPromoterStats($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		$statement = "SELECT * FROM `vw_promoters_stats`";
		if ( !is_null( $userfbid ) ) {
			$promoterfbid = trim($promoterfbid);
			$statement .= " WHERE `chosen_by_fbid` = '$userfbid'";
		}
		$statement .= ";";
		return static::$db->query($statement);
	}
	
	public static function getBenefitUsersByPromoter($userfbid = null, $eventfbid = null, $fbgender = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		$statement = "SELECT * FROM `vw_users_benefits`";
		if ( !is_null( $userfbid ) ) {
			$promoterfbid = trim($promoterfbid);
			$statement .= " WHERE `chosen_by_fbid` = '$userfbid'";
		}
		if( !is_null( $eventfbid ) ) {
			$eventfbid = trim($eventfbid);
			$statement .= " AND `eventfbid` = '$eventfbid'";
		}
		if( !is_null( $fbgender ) ) {
			$fbgender = trim($fbgender);
			$statement .= " AND `fbgender` = '$fbgender'";
		}
		$statement .= ";";
		return static::$db->query($statement);
	}
	
	/**
	 * Retorna qual o status de paquera o usuário definiu para alguém 
	 * 
	 * @todo Mover para a classe UltraCrush
	 * @todo Entender o porquê de $status ser um parâmetro.
	 * @param string $target_userfbid ID do usuário que foi alvo da paquera/amizade/fora.
	 * @param number $status		  Status da paquera. 0 = Nem pensar. 1 = Amizade. 2 = Tô afim.
	 * @return boolean|Ambigous <string, PDOStatement> Status da paquera. 0 = Nem pensar. 1 = Amizade. 2 = Tô afim.
	 */
	public static function getCrushStatus($target_userfbid, $status) {
		$current_userfbid = static::$user_profile['id'];
		if(!static::userfbidIsValid($current_userfbid) || !static::userfbidIsValid($target_userfbid)) {
			return false;
		}
		return static::$db->query("
SELECT *
FROM `users_crush`
WHERE `origin_userfbid` = '$current_userfbid' AND `target_userfbid` = '$target_userfbid';
");
	}
	
	/**
	 * Define um status de paquera do usuário atual para um alvo 
	 * 
	 * @todo Mover para a classe UltraCrush
	 * @param string $target_userfbid	ID do usuário que será alvo da paquera/amizade/fora
	 * @param number $status			Status da paquera. 0 = Nem pensar. 1 = Amizade. 2 = Tô afim.
	 * @return boolean|number			Falso se os ID dos usuário forem falsos ou o resultado da consulta.
	 */
	public static function usersCrush($target_userfbid, $status) {
		$current_userfbid = static::$user_profile['id'];
		if(!static::userfbidIsValid($current_userfbid) || !static::userfbidIsValid($target_userfbid)) {
			return false;
		}
		$crush_already_exists = static::$db->rowCount("
SELECT *
FROM `users_crush`
WHERE `origin_userfbid` = '$current_userfbid' AND `target_userfbid` = '$target_userfbid';
");
		if(!$crush_already_exists) {
			return static::$db->rowCount("INSERT INTO `users_crush` (`origin_userfbid`, `target_userfbid`, `status`) VALUES ('$current_userfbid', '$target_userfbid', '$status');");
		}
		else {
			return static::$db->rowCount("UPDATE `users_crush` SET `status` = '$status' WHERE `origin_userfbid` = '$current_userfbid' AND `target_userfbid` = '$target_userfbid';");
		}
	}
	
	/**
	 * Retorna a lista de paqueras/amizades/foras de um usuário de origem
	 * 
	 * @todo Mover para a classe UltraCrush
	 * @param string $userfbid ID do usuário. Se não for informado, é o ID do usuário atual.
	 * @return Ambigous <string, PDOStatement> A lista de paqueras/amizades/foras
	 */
	public static function getUserCrushes($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		return static::$db->query("SELECT * FROM `users_crush` WHERE `origin_userfbid` = '$userfbid'");
	}
	
	/**
	 * Retorna uma lista de pessoas que combinam com o usuário.
	 * 
	 * @todo Mover para a classe UltraCrush
	 * @param string $userfbid  ID do usuário. Se não for informado, é o ID do usuário atual.
	 * @return Ambigous <string, PDOStatement> A lista de pessoas que combinam com o usuário.
	 */
	public static function getCrushMatches($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		return static::$db->query("SELECT * FROM `vw_crush_match` WHERE `userfbid1` = '$userfbid' AND `status1` = 2 AND `status2` = 2;");
	}
	
	/**
	 * Retorna uma lista de pessoas que estão interessadas no usuário, mas que ele ainda não demonstrou interesse
	 * 
	 * @todo Mover para a classe UltraCrush
	 * @param string $userfbid ID do usuário. Se não for informado, é o ID do usuário atual.
	 * @return Ambigous <string, PDOStatement> A lista de pessoas nas 
	 */
	public static function getCrushTips($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		return static::$db->query("SELECT * FROM `vw_crush_match` WHERE `userfbid1` = '$userfbid' AND `status1` = 1 AND `status2` = 2");
	}
	
	public static function getCrushFriends($userfbid = null) {
		$userfbid = is_null($userfbid) ? static::$user_profile['id'] : $userfbid;
		return static::$db->query("SELECT * FROM `vw_crush_match` WHERE `userfbid1` = '$userfbid' AND `status1` = 1 AND `status2` = 1");
	}
	
	/**
	 * Testa se um usuário e outro combinam
	 * 
	 * @todo Mover para a classe UltraCrush
	 * @param string $userfbid1	ID de um dos usuários. Obrigatório.
	 * @param string $userfbid2	ID de um dos usuários. Obrigatório.
	 * @return Ambigous <string, PDOStatement>	Um resultset com o resultado da combinação, caso haja.
	 */
	public static function testCrushMatches($userfbid1, $userfbid2) {
		return static::$db->query("SELECT * FROM `vw_crush_crush` WHERE ((`target_userfbid` = '$userfbid1' AND `origin_userfbid` = '$userfbid2') OR (`target_userfbid` = '$userfbid2' AND `origin_userfbid` = '$userfbid1')) AND `status` = 2");
	}

} // end class FacebookAdapter