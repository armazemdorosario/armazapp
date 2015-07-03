<?php

namespace User\Model;

use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;

class UserTable extends Table {

	protected $tableName = 'users';
	protected $fetchObjectName;

	public function __construct(PDO $pdo) {
		$this->fetchObjectName = __NAMESPACE__ . '\User';
		parent::__construct($pdo, $this->tableName, $this->fetchObjectName);
	}

	public function getIDField() {
		return 'fbid';
	}

	/**
	 * User signup. Save her/his data on database
	 *
	 * @param User $user User object with data collected via form or API
	 * @return unknown Signup query result, if was possible.
	 */
	public function save($user, $autoIncrementIdField = false) {
		if(empty($user->fbid)) {
			throw new Exception('User Facebook ID cannot be empty', 500);
		}
		if(!$user->isFbidValid()) {
			throw new Exception('User Facebook ID is not valid', 501);
		}
		if(empty($user->name)) {
			throw new Exception('User full name cannot be empty', 502);
		}
		if(!$user->isNameValid()) {
			throw new Exception('User registered name is not valid', 503);
		}
		if(empty($user->fbname)) {
			throw new Exception('User Facebook name cannot be empty', 504);
		}
		if(!$user->isFbNameValid()) {
			throw new Exception('User Facebook name is not valid', 505);
		}
		if(empty($user->id_card)) {
			throw new Exception('User ID card cannot be empty', 506);
		}
		if(!$user->isIdCardValid()) {
			throw new Exception('User ID card number is not valid: ' . $user->id_card, 507);
		}
		try {
			$idCardAlreadyExists = $this->fetchBy('id_card', $user->id_card);
		}
		catch(\Exception $e) {
			throw new Exception('Cannot check for ID card existence. Database schema seems to be empty or invalid. ' . $e->getMessage(), 515);
		}
		if($idCardAlreadyExists) {
			throw new Exception('User ID card number already exists on database', 508);
		}
		if(empty($user->ir_number)) {
			throw new Exception('User individual registration number cannot be empty', 509);
		}
		if(!method_exists($user, 'isIrNumberValid')) {
			throw new Exception('Cannot check if user individual registration number is valid. Check method was not found.', 510);
		}
		if(!$user->isIrNumberValid()) {
			throw new Exception('User individual registration number is not valid', 511);
		}
		if($this->fetchBy('ir_number', $user->ir_number)) {
			throw new Exception('User individual registration number already exists on database', 514);
		}
		if(empty($user->fbgender)) {
			throw new Exception('User Facebook gender cannot be empty', 512);
		}
		if(!$user->isGenderValid()) {
			throw new Exception('User Facebook gender is not valid', 513);
		}
		// Created date will change once
		if(empty($user->date_created)) {
			$date_created = date_create();
			$user->date_created = $date_created->format('Y-m-d H:i:s');
		}
		// Date will always be updated
		$date_updated = date_create();
		$user->date_updated = $date_updated->format('Y-m-d H:i:s');
		// false: ID key don't auto increment
		return parent::save($user, false);
	}

}
