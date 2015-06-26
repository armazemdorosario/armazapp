<?php

namespace BenefitType\Model;

class BenefitType {
	
	public $idtype;
	public $name;

	public function exchangeArray(array $data) {
		$definition = array(
			'idtype' 	=> \FILTER_SANITIZE_NUMBER_INT,
			'name' 		=> \FILTER_SANITIZE_STRING,
		);
		foreach (filter_var_array($data, $definition) as $key => $value) {
			$this->$key = empty($value) ? null : $value;
		}
		return $this;
	}

}
