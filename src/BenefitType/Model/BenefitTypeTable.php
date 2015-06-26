<?php

namespace BenefitType\Model;

use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;

class BenefitTypeTable extends Table {
	
	protected $tableName = 'benefit_types';
	protected $fetchObjectName;

	public function __construct(PDO $pdo) {
		$this->fetchObjectName = __NAMESPACE__ . '\BenefitTypeTable';
		parent::__construct($pdo, $this->tableName, $this->fetchObjectName);
	}

	public function save(BenefitType $benefitType) {
		if(empty($benefitType->name)) {
			throw new Exception('Benefit type name cannot be empty', 40);
		}
		return parent::save($benefitType);
	}

}
