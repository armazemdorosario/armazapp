<?php

namespace Benefit\Model;

use \DateTime;

class Benefit {

    public $eventfbid;
    public $benefit_type;
    public $num_people_claimed = null;
    public $num_people_chosen = null;
    public $accepted_gender = null;
    public $max_num_people_chosen = null;
    public $status;
    public $expiration_date;
    public $photo = null;
    public $b_date_created;
    public $object;
    public $featured = 0;
    public $claim_expiration_date = null;

    protected static $patterns = array(
        'fb_photo' => '(https\:\/\/(\w|\-|\.)*\.fbcdn\.net\/(\w|\-|\/|\.)*\?(\w*\=(\d|\w)*\&?)*)',
    );

    public function __construct($includeCalculatedFields = false) {
        if(true === $includeCalculatedFields) {
             $this->generateCalculatedFields();
        }
    }

    private function generateCalculatedFields() {
        $calculatedFields = array('description', 'is_date_only', 'name', 'owner', 'privacy', 'start_time', 'timezone', 'updated_time', 'info_text', 'is_full_vip_list', 'cdn_photo', 'db_expiration_date', 'progress');
            foreach ($calculatedFields as $field) {
                if(!property_exists($this, $field)) {
                    $this->$field = null;
                }
            }

            $this->is_full_vip_list = $this->isFullVipList();
            $this->cdn_photo = preg_match(Benefit::$patterns['fb_photo'], $this->photo) > 0 ? $this->photo : 'https://i0.wp.com/' . str_replace(array('https://', 'http://'), '', $this->photo);
            $this->progress = $this->getProgress();
            $this->remaining_percentage = $this->getRemainingPercentage() > 100 ? 100 : $this->getRemainingPercentage();
    }

    public function exchangeArray(array $data) {
        $definition = array(
            'eventfbid' => \FILTER_SANITIZE_STRING,
            'benefit_type' => \FILTER_SANITIZE_NUMBER_INT,
            'num_people_claimed' => \FILTER_SANITIZE_NUMBER_INT,
            'num_people_chosen' => \FILTER_SANITIZE_NUMBER_INT,
            'accepted_gender' => \FILTER_SANITIZE_STRING,
            'max_num_people_chosen' => \FILTER_SANITIZE_NUMBER_INT,
            'status' => \FILTER_SANITIZE_NUMBER_INT,
            'expiration_date' => \FILTER_SANITIZE_STRING,
            'photo' => \FILTER_SANITIZE_URL,
            'b_date_created' => \FILTER_SANITIZE_STRING,
            'object' => \FILTER_SANITIZE_NUMBER_INT,
            'featured' => \FILTER_SANITIZE_NUMBER_INT,
            'claim_expiration_date' => \FILTER_SANITIZE_STRING,
        );
        foreach (filter_var_array($data, $definition) as $key => $value) {
            $this->$key = empty($value) ? $this->$key : $value;
        }
        return $this;
    }

    public function exchangeFacebookArray(array $data) {
        if(!property_exists($this, 'is_date_only')) {
            $this->generateCalculatedFields();
        }
        if($data['id'] !== $this->eventfbid) {
            return false;
        }
        unset($data['id']);
        foreach ($data as $key => $value) {
            $this->$key = empty($value) ? $this->$key : $value;
        }
        $this->db_start_time = $this->getStartTime()->format('Y-m-d H:i:s');
        $this->db_expiration_date = $this->getExpirationDate()->format('Y-m-d H:i:s');
    }

    public function setIfCurrentGenderCanAttend($value) {
        $this->current_gender_can_attend = $value;
    }

    public function setIfCurrentUserAttended($value) {
        $this->current_user_attended = $value;
    }

    public function setInfoText($value) {
        $this->info_text = $value;
    }

    public function getExpirationDate() {
        return new DateTime($this->expiration_date);
    }

    public function getParsedExpirationDate() {
        return date_parse($this->getExpirationDate());
    }

    public function getDateCreated() {
        return new DateTime($this->timestamp);
    }

    public function getParsedDateCreated() {
        return date_parse($this->getTimestamp());
    }

    public function getClaimExpirationDate() {
        return new DateTime($this->claim_expiration_date);
    }

    public function getParsedClaimExpirationDate() {
        return date_parse($this->getClaimExpirationDate());
    }

    public function getStartTime() {
        return new DateTime($this->start_time);
    }

    public function getParsedStartTime() {
        return date_parse($this->start_time);
    }

    public function isFullVipList() {
        return (1 === intval($this->benefit_type)) && ($this->num_people_claimed >= $this->max_num_people_chosen);
    }

    public static function isAnyEventFbidValid($eventfbid) {
        return isset($eventfbid) && !empty($eventfbid) && !is_null($eventfbid) && in_array(strlen($eventfbid), array(15, 16));
    }

    public function isEventFbidValid() {
        return Benefit::isAnyEventFbidValid($this->eventfbid);
    }

    public static function isAnyBenefitTypeValid($benefit_type) {
        $benefit_type = intval($benefit_type);
        return 1 === $benefit_type || 2 === $benefit_type;
    }

    public static function sanitizeAnyBenefitType($benefit_type) {
        return Benefit::isAnyBenefitTypeValid($benefit_type) ? intval($benefit_type) : null;
    }

    public function sanitizeBenefitType() {
        return Benefit::sanitizeAnyBenefitType($this->benefit_type);
    }

    public function getProgress() {
        return intval($this->max_num_people_chosen)>0 ? intval($this->num_people_claimed)/intval($this->max_num_people_chosen) * 100 : 0;
    }

    public function getRemainingPercentage() {
        $percentage = 100 - $this->getProgress();
        return $percentage > 100 ? 100 : $percentage;
    }
}
