<?php
require_once('Validator.php');
class StringValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          return $value;
     }
}
