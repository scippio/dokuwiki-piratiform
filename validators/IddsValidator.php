<?php
require_once('RegexpValidator.php');
class IddsValidator extends RegexpValidator {
     public function validate($value){
          $this->setOption('regexp','^[a-z0-9]{7}$');
          parent::validate($value);
          return $value;
     }
}
