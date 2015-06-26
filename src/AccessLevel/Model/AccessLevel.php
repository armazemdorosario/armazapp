<?php
namespace AccessLevel\Model;

class AccessLevel {
	
	public $id_access_level;
	public $name;

	public function exchangeArray(array $data) {
		$definition = array(
			'id_access_level' 	=> \FILTER_SANITIZE_NUMBER_INT,
			'name' 				=> \FILTER_SANITIZE_STRING,
		);
		foreach (filter_var_array($data, $definition) as $key => $value) {
			$this->$key = empty($value) ? null : $value;
		}
		return $this;
	}
}
