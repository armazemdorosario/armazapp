<?php

namespace UserBenefit\Model;

use \Benefit\Model\Benefit;
use \User\Model\User;
use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;
use \UserStats\Model\UserStatsTable;

class UserBenefitTable extends Table {

	protected $tableName = 'users_benefits';
	protected $fetchObjectName;

	public function __construct(PDO $pdo) {
		$this->fetchObjectName = __NAMESPACE__ . '\UserBenefit';
		parent::__construct($pdo, $this->tableName, $this->fetchObjectName);
	}

	public function getIDField() {
		return array('userfbid', 'eventfbid', 'benefit_object');
	}

	public function fetch($data) {
		if(is_numeric($data)) {
			throw new Exception('Insuficient data to fetch UserBenefit table. Please use an array as argument for fetch() function.', 610);
		}
		return parent::fetch($data);
	}

	public function fetchUsersByEvent($eventfbid, $benefit_type = 1, $includePrivate = false, $orderby = '') {
		if(!Benefit::isAnyEventFbidValid($eventfbid)) {
			throw new Exception('Cannot fetch users-benefits data because benefit ID is invalid', 611);
		}
		$where = "`eventfbid` = '$eventfbid' AND `benefit` = '$benefit_type'";
		$where .= true === $includePrivate ? '' : " AND `private` = '0'";
		$where .= empty($orderby) ? '' : " ORDER BY $orderby";
		try {
			return parent::fetchAll($where);
		}
		catch(\Exception $e) {
			switch ($e->getCode()) {
				case 1054:
					throw new Exception('Error when trying to get users from an event. Database query must be wrong: ' . $e->getMessage());
					break;

				default:
					throw new Exception('Error when trying to get users from an event.' . $e->getMessage() . ' ' . $e->getCode());
					break;
			} // end switch
		} // end throw catch
	} // end function fetchUsersByEvent

	/**
	 * Inserts a $userfbid on $eventfbid benefit (VIP List or Sweepstake)
	 */
	public function save($userBenefit, $autoIncrementIdField = false) {
		if(empty($userBenefit->userfbid)) {
			throw new Exception('User Facebook ID cannot be empty', 600);
		}
		if(!User::isAnyFbidValid($userBenefit->userfbid)) {
			throw new Exception('Cannot add user to benefit. User Facebook ID is invalid', 601);
		}
		if(empty($userBenefit->eventfbid)) {
			throw new Exception('Facebook Event ID cannot be empty', 602);
		}
		if(empty($userBenefit->benefit)) {
			throw new Exception('Benefit type ID cannot be empty', 603);
		}
		if(!filter_var($userBenefit->benefit, \FILTER_VALIDATE_INT)) {
			throw new Exception('Benefit type ID is not a valid number', 604);
		}
		if(empty($userBenefit->benefit_object)) {
			throw new Exception('Benefit object ID cannot be empty', 605);
		}
		if(!filter_var($userBenefit->benefit_object, \FILTER_VALIDATE_INT)) {
			throw new Exception('Benefit object ID is not a valid number', 606);
		}
		$userStatsTable = new UserStatsTable($this->pdo);
		if(!$userStatsTable->canUserEnterOnVipLists($userBenefit->userfbid)) {
			throw new Exception('Error when trying to add the user to benefit. User missed some event recently, he/she is on block list.', 607);
		}
		if($this->exists($userBenefit->benefit, $userBenefit->userfbid, $userBenefit->eventfbid, $userBenefit->benefit_object)) {
			throw new Exception('User ' . $userBenefit->userfbid . ' tried to enter more than one time on benefit ' . $userBenefit->eventfbid, 608);
		}
		// Created date will change once
		if(empty($userBenefit->ub_date_created)) {
			$ub_date_created = new \DateTime('now');
			$userBenefit->ub_date_created = $ub_date_created->format('Y-m-d H:i:s');
		}
		return parent::save($userBenefit);
	}

	public function exists($benefit_type, $userfbid, $eventfbid, $benefit_object = 1) {

		$benefit_type = Benefit::sanitizeAnyBenefitType($benefit_type);
		$userfbid = User::sanitizeAnyFbid($userfbid);

		if(!Benefit::isAnyBenefitTypeValid($benefit_type)) {
			Debugger:log('Cannot check if someone claimed benefit. Benefit type ID not informed or invalid: ' . $benefit_type);
			return false;
		}
		if(!User::isAnyFbidValid($userfbid)) {
			Debugger::log('Cannot check if someone claimed benefit. User Facebook ID not informed or invalid: ' . $userfbid);
			return false;
		}
		if(!Benefit::isAnyEventFbidValid($eventfbid)) {
			Debugger::log('Cannot check if a user claimed benefit: Event FBID not informed or invalid: ' . $eventfbid);
			return false;
		}
		$result = parent::fetchAll("`userfbid` = '{$userfbid}' AND `eventfbid` = '{$eventfbid}' AND `benefit` = '{$benefit_type}' AND `benefit_object` = '{$benefit_object}'");
		return empty($result) ? false : true;
	}

}
