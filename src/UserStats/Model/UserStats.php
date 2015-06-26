<?php

namespace UserStats\Model;

class UserStats {

	public $fbid;
	public $name;
	public $benefit;
	public $chosen;
	public $actually_attended;
	public $count;

	public function exchangeArray(array $data) {
		$definition = array(
			'fbid' 			=> \FILTER_SANITIZE_STRING,
			'name' 			=> \FILTER_SANITIZE_STRING,
			'benefit' 		=> \FILTER_SANITIZE_NUMBER_INT,
			'chosen'		=> \FILTER_SANITIZE_NUMBER_INT,
			'actually_attended' => \FILTER_SANITIZE_NUMBER_INT,
			'count' 		=> \FILTER_SANITIZE_NUMBER_INT,
		);
		foreach (filter_input_array($data, $definition) as $key => $value) {
			if(property_exists($this, $key)) {
				$this->$key = empty($value) ? $this->$key : $value;
			} // end if
		} // end foreach
	} // end function exchangeArray

} // end class UserStats
