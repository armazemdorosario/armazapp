<?php

namespace User\Model;

use \DateTime;

class User {

	public $fbid;
	public $name;
	public $id_card;
	public $ir_number;
	public $fbname;
	public $fbgender;

	/*
	 * User access level
	 *
	 *
	 * 0 = Registered user (can join VIP Lists and Sweepstakes)
	 * 1 = Promoter (can add registered users to Promoter VIP Lists and see stats)
	 * 2 = Cannot join VIP Lists and never will win a ticket sweepstake
	 * 3 = Administrator. Can see private claims, download benefit data and create/change benefit status.
	 * 4 = Super Administrator. Can change/delete benefits and users' data.
	 */
	public $access_level = 0;

	public $trust_level = null;
	public $date_created;
	public $date_updated;
	public static $patterns = array(
		'fbid' 		=> '([\d]{15,16}|[1]{1}[0-8]{1}\d{8}|[5-8]{1}\d{8})',
		'name' 		=> '((\w|[\u00C0-\u00FC]){3,15})\s(\s{0,2}(\w|[\u00C0-\u00FC]){1,10}\s?\.?){1,}',
		'id_card'   => '(([R]?[G]?\-?)?([M,m]{1}[G,g]?\s?(\_?|\:?\-?|\.?|\–?)\s?)?((\d{6,10})|(\d{2}\w{2}\d{5})|(\d{2}\.?\d{3}\.?\d{3}-?\d{1})|(\d{1,2}(\.?|\-?)(\s?)\d{3}(\.?|\-?)(\s?)\d{3})|(\d{4}\s?\d{6})|(\d{7}\-?\d{1})|(\d{2}\s?\.?\d{3}\.?\d{3})|(\d{3}\-?\d{3}\.?\d{2}))(\-(\d{1,2}|[x]))?(\s?)(\-?)(([S,s]{2}[P,p]{1})?(\s?|\/?)\w{2})?)',
		'ir_number' => '(\d{3}\.{0,1}\d{3}\.{0,1}\d{3}-{0,1}\d{2})',
		'fbname' 	=> '((\w|[\u00C0-\u00FD]){2,10}|((\w|[\u00C0-\u00FD]){2,6}\-?\w{2,7}){2,5})(\s(\w|[\u00C0-\u00FC]){1,11}\.?){1,4}',
	);
	private static $nameBlacklistedWords = array('brs', 'rep', 'republica', 'república');
	private static $idNumbersBlacklistedChars = array('(', ')', ' ', '.', '-', '/', 'á', 'í', '@', ':', '–', '!', ',', '_');

	/**
	 * @todo Create sanitization functions for FBID & Brazilian ID Card
	 */
	public function exchangeArray(array $data) {
		$definition = array(
			'fbid' 			=> \FILTER_SANITIZE_STRING,
			'name' 			=> \FILTER_SANITIZE_STRING,
			'id_card' 		=> \FILTER_SANITIZE_STRING,
			'ir_number'		=> \FILTER_SANITIZE_STRING,
			'fbname' 		=> \FILTER_SANITIZE_STRING,
			'fbgender' 		=> \FILTER_SANITIZE_STRING,
			'access_level' 	=> \FILTER_SANITIZE_NUMBER_INT,
			'date_created' 	=> \FILTER_SANITIZE_STRING,
		);
		foreach (filter_var_array($data, $definition) as $key => $value) {
			if(property_exists($this, $key)) {
				$this->$key = empty($value) ? $this->$key : $value;
			}
		}
		$this->fbid = User::sanitizeFbid($this->fbid);
		$this->name = User::sanitizeName($this->name);
		$this->id_card = User::sanitizeIdCard($this->id_card);
		$this->ir_number = User::sanitizeIrNumber($this->ir_number);
		$this->fbname = User::sanitizeName($this->fbname);
		$this->trust_level = $this->getTrustLevel();
	}

	public function getCreatedDate() {
		return new DateTime($this->date_created);
	}

	public function getParsedCreatedDate() {
		return date_parse($this->getCreatedDate());
	}

	public function getUpdatedDate() {
		return new DateTime($this->date_updated);
	}

	public function getParsedUpdatedDate() {
		return date_parse($this->getUpdatedDate());
	}

	public static function sanitizeAnyFbid($userfbid) {
		$preg_match_all = preg_match_all('!\d+!', $userfbid, $matches);
		if($preg_match_all) {
			if(is_array($matches) && isset($matches[0]) && is_array($matches[0])) {
				return trim($matches[0][0]);
			}
		}
		return null;
	}

	public function sanitizeFbid() {
		return User::sanitizeAnyFbid($this->fbid);
	}

	/**
	 * Sanitize user full name, removing accents and correcting capitalization.
	 *
	 * @param string $name Name
	 * @return string Sanitized name
	 */
	public static function sanitizeName($name) {
		$from			 = array('.', 'Á', 'á', 'Ã', 'ã', 'â', 'ç', 'é', 'ê', 'Í', 'í', 'ñ', 'õ', 'ú', 'Ú');
		$to				 = array(' ', 'A', 'a', 'A', 'a', 'a', 'c', 'e', 'e', 'I', 'i', 'n', 'o', 'u', 'U');
		$name			 = str_replace($from, $to, $name);
		$words			 = str_word_count($name, 1);
		$formatted_name	 = array();
		foreach ( $words as $word ) {
			$word = strtolower( $word );
			if(!in_array($word, User::$nameBlacklistedWords)) {
				switch ( $word ) {
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
						$formatted_name[] = ucwords( $word );
						break;
				}
			}
		}
		return implode( ' ', $formatted_name );
	}

	/**
	 * Tests if Facebook user ID is valid.
	 *
	 * To be valid, it has to be 9, 10 or 15 of length. If 10 ou 15, must start with 1.
	 *
	 * @param string $userfbid
	 * @return boolean
	 */
	public static function isAnyFbidValid($userfbid) {
		$preg_match_all = preg_match_all('/' . User::$patterns['fbid'] . '/', $userfbid, $matches);
		return $preg_match_all ? true : false;
	}

	public function isFbidValid() {
		return User::isAnyFbidValid($this->fbid);
	}

	public static function isAnyNameValid($name) {
		return isset($name) && !empty($name) && false !== strpos($name, ' ');
	}

	/**
	 * Tests if user full name is valid.
	 *
	 * To be valid, it must have at least one space character
	 */
	public function isNameValid() {
		return User::isAnyNameValid($this->name);
	}

	/**
	 * Tests if user full name is valid.
	 *
	 * To be valid, it must have at least one space character
	 */
	public function isFbNameValid() {
		return User::isAnyNameValid($this->fbname);
	}

	/**
	 * Calculates the trust level of user names
	 *
	 * This function compares the database name with the Facebook name
	 *
	 * @param string $name		Required. The name registered by user on app.
	 * @param string $fbname	Required. The name registered by user on Facebook
	 * @return number			A value between 0 and 100.
	 */
	public function getTrustLevel() {
		$name = User::sanitizeName($this->name);
		$fbname = User::sanitizeName($this->fbname);
		$name_words = str_word_count($this->name, 2);
		$fbname_words = str_word_count($this->fbname, 2);

		if(empty($name)||empty($fbname)) {
			return 0;
		}
		if(count($name_words)<2 || count($fbname_words)<2) {
			return 0.01;
		}
		foreach ($name_words as $word) {
			if(in_array(strtolower($word), User::$nameBlacklistedWords)) {
				return 0.01;
			}
		}
		foreach ($fbname_words as $word) {
			if(in_array(strtolower($word), User::$nameBlacklistedWords)) {
				return 0.01;
			}
		}
		similar_text($name, $fbname, $percent);
		return number_format($percent, 2);
	}

	public function isIdCardValid() {
		preg_match_all('/'.User::$patterns['id_card'].'/', $this->id_card, $matches);
		return is_array($matches) && isset($matches[0]) && isset($matches[0][0]);
	}

	public static function sanitizeAnyIdCard($id_card) {
		preg_match_all('/'.User::$patterns['id_card'].'/', $id_card, $matches);
		if(is_array($matches) && isset($matches[0]) && isset($matches[0][0])) {
			return strtoupper(str_replace(User::$idNumbersBlacklistedChars, '', $matches[0][0]));
		}
		return null;
	}

	public function sanitizeIdCard() {
		return User::sanitizeAnyIdCard($this->id_card);
	}

	public static function sanitizeAnyIrNumber($ir_number) {
		preg_match_all('/'.User::$patterns['ir_number'].'/', $ir_number, $matches);
		if(is_array($matches) && isset($matches[0]) && isset($matches[0][0])) {
			return strtoupper(str_replace(User::$idNumbersBlacklistedChars, '', $matches[0][0]));
		}
		return null;
	}

	public function isIrNumberValid() {
		return preg_match_all('/'.User::$patterns['ir_number'].'/', $this->ir_number);
	}

	public function sanitizeIrNumber() {
		return User::sanitizeAnyIrNumber($this->ir_number);
	}

	public function isGenderValid() {
		return 'male' === $this->fbgender || 'female' === $this->fbgender || '' === $this->fbgender;
	}

	public function sanitizeGender() {
		return isGenderValid() ? $this->fbgender : null;
	}

	/**
	* Retrieves user access level
	*
	* @return boolean|number User access level, starting from 0 (Registered User)
	*/
	public function getUserLevel() {
		return intval($this->access_level);
	}

	/**
	* User is promoter?
	*
	* Uses $access_level object property to check if user is a promoter (level 1)
	*
	* @return boolean Whether user is promoter
	*/
	public function isPromoter() {
		return 1 === $this->access_level;
	}

	/**
	* User has administrative access?
	*
	* Uses $access_level object property to check if user is administrator
	*
	* @return boolean Whether user is administrator (4) or super administrator (5)
	*/
	public function isAdministrator() {
		return $this->access_level >= 4;
	}

}
