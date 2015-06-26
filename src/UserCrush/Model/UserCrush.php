<?php

namespace UserCrush\Model;

class UserCrush {

	public $origin_userfbid;
	public $target_userfbid;
	public $status;

	public function exchangeArray(array $data) {
		$definition = array(
			'origin_userfbid' => \FILTER_SANITIZE_STRING,
			'target_userfbid' => \FILTER_SANITIZE_STRING,
			'status' => \FILTER_SANITIZE_NUMBER_INT,
		);
		foreach (filter_var_array($data) as $key => $value) {
			$this->$key = empty($value) ? null : $value;
		}
	}

}
