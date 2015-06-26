<?php

namespace BenefitObject\Model;

class BenefitObject {
	
	public $idobject;
	public $objectname;
	public $objectdescription = null;
	public $provided_by;
	public $plural_name;

	public function exchangeArray(array $data) {
		$definition = array(
			'idobject' => \FILTER_SANITIZE_NUMBER_INT,
			'objectname' => \FILTER_SANITIZE_STRING,
			'objectdescription' => \FILTER_SANITIZE_STRING,
			'provided_by' => \FILTER_SANITIZE_STRING,
			'plural_name' => \FILTER_SANITIZE_STRING,
		);
		foreach (filter_var_array($data, $definition) as $key => $value) {
			$this->$key = empty($value) ? null : $value;
		}
		return $this;
	}

}
