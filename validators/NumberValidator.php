<?php
require_once('Validator.php');
class NumberValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          if(!preg_match('/^[0-9]+$/',$value)){
               $this->setError('Chybný tvar čísla');
               throw new ValidatorException();
          }
          return $value;
     }
     public function getDbType(){
          return 'integer';
     }
}
