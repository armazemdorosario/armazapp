<?php
namespace Benefit\Model;

use \Eventzapp\Exception;
use \Japhpy\Table;
use \PDO;

class BenefitTable extends Table {

    protected $tableName = 'benefits';
    protected $fetchObjectName;
    protected static $defaultOrder = 'ORDER BY `status` ASC, `featured` DESC, `expiration_date` ASC';

    public function __construct(PDO $pdo) {
        $this->fetchObjectName = __NAMESPACE__ . '\Benefit';
        parent::__construct($pdo, $this->tableName, $this->fetchObjectName);
    }

    public function getIDField() {
        return array('eventfbid', 'benefit_type', 'object');
    }

    private function fetchAllBenefitsByType($benefit_type) {
        return parent::fetchAll('`benefit_type` = ' . intval($benefit_type) . ' ' . BenefitTable::$defaultOrder);
    }

    private function fetchAllActiveBenefitsByType($benefit_type) {
        return parent::fetchAll('`benefit_type` = ' . intval($benefit_type) . ' AND `status` <> 0 ' . BenefitTable::$defaultOrder);
    }

    public function fetchAllVipLists() {
        return $this->fetchAllBenefitsByType(1);
    }

    public function fetchAllActiveVipLists() {
        return $this->fetchAllActiveBenefitsByType(1);
    }

    public function fetchAllSweepstakes() {
        return $this->fetchAllBenefitsByType(2);
    }

    public function fetchAllActiveSweepstakes() {
        return $this->fetchAllActiveBenefitsByType(2);
    }

    public function save($benefit, $autoIncrementIdField = false) {
        if (empty($benefit->eventfbid)) {
            throw new Exception('Event ID of benefit cannot be empty', 20);
        }
        if (empty($benefit->benefit_type)) {
            throw new Exception('Benefit type cannot be empty', 21);
        }
        if (!filter_var($benefit->benefit_type, \FILTER_VALIDATE_INT)) {
            throw new Exception('Benefit type is not a valid number', 22);
        }
        if (empty($benefit->status) || !filter_var($benefit->status, \FILTER_VALIDATE_INT)) {
            throw new Exception('Benefit status is not valid', 23);
        }
        if (empty($benefit->expiration_date)) {
            throw new Exception('Benefit expiration date cannot be empty', 24);
        }
        if (empty($benefit->object)) {
            throw new Exception('Benefit object ID cannot be empty', 25);
        }
        if (!filter_var($benefit->object, \FILTER_VALIDATE_INT)) {
            throw new Exception('Benefit object ID is not valid', 26);
        }
        return parent::save($benefit, false);
    }

}
