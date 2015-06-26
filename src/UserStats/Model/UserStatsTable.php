<?php

namespace UserStats\Model;

use \Eventzapp\Debugger;
use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;
use \User\Model\User;

class UserStatsTable extends Table {
	
	protected $tableName = 'vw_users_stats';
	
	public function __construct(PDO $pdo) {
		parent::__construct($pdo, $this->tableName, 'stdClass');
	}

	public function getIDField() {
		return 'fbid';
	}

	/**
	 * Cannot be static because the query depends on $db
	 */
	public function getUserRecentFails($userfbid) {
		$userfbid = User::sanitizeAnyFbid($userfbid);
		$count = parent::fetchColumnCount('actually_attended', "`fbid` = '$userfbid' AND `actually_attended` = '0'");
		return intval($count);
	}

	/**
	 * Cannot be static because the query depends on $db
	 */
	public function canUserEnterOnVipLists($userfbid) {
		$userfbid = User::sanitizeAnyFbid($userfbid);
		if(!User::isAnyFbidValid($userfbid)) {
			throw new Exception('User Facebook ID is invalid');
		}
		try {
			$userRecentFails = intval($this->getUserRecentFails($userfbid));
		}
		catch(\Exception $e) {
			Debugger::log('There is a failure on querying and counting user recent fails. App will resume, but fix this.');
			return true;
		}
		return 0 === $userRecentFails;
	}

	final public function save($object, $autoIncrementIdField = false) {
		throw new Exception('This view does not contain an unique column. Save feature is not available.');
	}

}
