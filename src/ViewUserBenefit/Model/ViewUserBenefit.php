<?php

namespace ViewUserBenefit\Model;

class ViewUserBenefit {

  /**
   * ID of user related to benefit
   * @var integer Stores the Facebook ID (FBID) of user associated to benefit
   */
  public $fbid;
  public $name;
  public $fbname;
  public $fbgender;
  public $access_level;
  public $trust_level;
  public $userfbid;
  public $ub_date_created;
  public $eventfbid;
  public $private;

  /**
   * ID of benefit type
   * @var integer Stores the benefit type (1 for VIP List, 2 for Sweepstakes...)
   */
  public $benefit/*type*/;

  public $chosen;
  public $actually_attended;

  /**
   * Status of associated benefit
   * @var 0 for unpublished, 1 for published, 2 to closed, 3 to promoter list...
   */
  public $status;

  public $chosen_by_fbid;

  public function exchangeArray(array $data) {
    $definition = array(
      'fbid'              => \FILTER_SANITIZE_STRING,
      'name'              => \FILTER_SANITIZE_STRING,
      'fbname'            => \FILTER_SANITIZE_STRING,
      'fbgender'          => \FILTER_SANITIZE_STRING,
      'access_level'      => \FILTER_SANITIZE_NUMBER_INT,
      'trust_level'       => \FILTER_SANITIZE_NUMBER_FLOAT,
      'userfbid'          => \FILTER_SANITIZE_STRING,
      'ub_date_created'   => \FILTER_SANITIZE_STRING,
      'eventfbid'         => \FILTER_SANITIZE_STRING,
      'private'           => \FILTER_SANITIZE_STRING,
      'benefit'/*type*/   => \FILTER_SANITIZE_NUMBER_INT,
      'chosen'            => \FILTER_SANITIZE_NUMBER_INT,
      'actually_attended' => \FILTER_SANITIZE_NUMBER_INT,
      'status'            => \FILTER_SANITIZE_NUMBER_INT,
    );
    foreach (filter_var_array($data, $definition) as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = empty($value) ? $this->$key : value;
      } // end if
    } // end foreach
  } // end function exchangeArray

  public function __toString() {
    return json_encode($this);
  }

} // end class
