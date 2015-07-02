<?php

namespace ViewUserBenefit\Model;

use \PDO;
use \UserBenefit\Model\UserBenefitTable;

class ViewUserBenefitTable extends UserBenefitTable {

  public function __construct(PDO $pdo) {
    parent::__construct($pdo);
    $this->tableName = 'vw_users_benefits';
    $this->fetchObjectName = __NAMESPACE__ . '\ViewUserBenefit';
  }

  final public function save($object, $autoIncrementIdField = false) {
    throw new Exception('This view is read-only. Save feature is not available.');
  }

}
