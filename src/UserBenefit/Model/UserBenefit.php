<?php

namespace UserBenefit\Model;

use \DateTime;

class UserBenefit {

	/**
	 * ID of user being inserted on list
	 *
	 * @var string Stores the user ID to be inserted on list. Required.
	 */
	public $userfbid;
	public $eventfbid;
	public $private = 0;
	public $benefit;
	public $chosen = 0;
	public $ub_date_created;
	public $actually_attended = 1;
	/**
	 * User Facebook ID from that user that is inserting this user on list
	 *
	 * @var string Stores the User ID that is inserting someone in the list. If empty, it will be the same ID of inserted user.
	 */
	public $chosen_by_fbid = null;
	public $benefit_object = 1;

	public function exchangeArray(array $data) {
		$definition = array(
			'userfbid' 					=> \FILTER_SANITIZE_STRING,
			'eventfbid' 				=> \FILTER_SANITIZE_STRING,
			'benefit_object' 		=> \FILTER_SANITIZE_NUMBER_INT,
			'private' 					=> \FILTER_SANITIZE_NUMBER_INT,
			'benefit' 					=> \FILTER_SANITIZE_NUMBER_INT,
			'chosen' 						=> \FILTER_SANITIZE_NUMBER_INT,
			'actually_attended' => \FILTER_SANITIZE_NUMBER_INT,
			'chosen_by_fbid' 		=> \FILTER_SANITIZE_STRING,
			'ub_date_created' 	=> \FILTER_SANITIZE_STRING,
		);
		foreach (filter_var_array($data, $definition) as $key => $value) {
			if(property_exists($this, $key)) {
				$this->$key = empty($value) ? $this->$key : $value;
			}
		}
	}

	public function getCreatedDate() {
		return new DateTime($this->ub_date_created);
	}

	public function getParsedCreatedDate() {
		return date_parse($this->getCreatedDate());
	}
}
