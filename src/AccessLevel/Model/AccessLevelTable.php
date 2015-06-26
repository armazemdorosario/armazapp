<?php

namespace AccessLevel\Model;

use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;

class AccessLevelTable extends Table {

	protected $tableName = 'access_levels';
	protected $fetchObjectName;

	public function __construct(PDO $pdo) {
		$this->fetchObjectName = __NAMESPACE__ . '\AccessLevel';
		parent::__construct($pdo, $this->tableName, $this->fetchObjectName);
	}

	public function save(AccessLevel $accessLevel) {
		if(empty($accessLevel->name)) {
			throw new Exception('Name of access level cannot be empty', 10);
		}
		return parent::save($accessLevel);
	}

}
