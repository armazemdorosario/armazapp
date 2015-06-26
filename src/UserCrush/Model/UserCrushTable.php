<?php

namespace UserCrush\Model;

use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;

class UserCrushTable {

	protected $tableName = 'users_crush';
	protected $fetchObjectName;

	public function __construct(PDO $pdo) {
		$this->fetchObjectName = __NAMESPACE__ . '\UserCrush';
		parent::__construct($pdo, $this->tableName, $this->fetchObjectName);
	}

	public function save(UserCrush $userCrush) {
		if(empty($userCrush->origin_userfbid)) {
			throw new Exception('Origin user Facebook ID cannot be empty', 70);
		}
		if(empty($userCrush->target_userfbid)) {
			throw new Exception('Origin user Facebook ID cannot be empty', 71);
		}
		if(empty($userCrush->status)) {
			throw new Exception('Crush status cannot be empty', 72);
		}
		if(!filter_var($userCrush->status, \FILTER_VALIDATE_INT)) {
			throw new Exception('Crush status is not a valid integer', 73);
		}
		return parent::save($userCrush);
	}

}
