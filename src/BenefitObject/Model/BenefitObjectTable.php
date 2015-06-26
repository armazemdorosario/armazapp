<?php

namespace BenefitObject\Model;

use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;

class BenefitObjectTable extends Table {

	protected $tableName = 'benefits_object';
	protected $fetchObjectName;

	public function __construct(PDO $pdo) {
		$this->fetchObjectName = __NAMESPACE__ . '\BenefitObjectTable';
		parent::__construct($pdo, $this->tableName, $this->fetchObjectName);
	}
	
	public function save(BenefitObject $benefitObject) {
		if(empty($benefitObject->objectname)) {
			throw new Exception('Benefit object singular name cannot be empty', 30);
		}
		if(empty($benefitObject->provided_by)) {
			throw new Exception('Benefit object "provided_by" field cannot be empty', 31);			
		}
		if(empty($benefitObject->plural_name)) {
			throw new Exception('Benefit object plural name cannot be empty', 32);
		}
		return parent::save($benefitObject);
	}

}
