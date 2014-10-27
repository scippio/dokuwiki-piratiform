<?php
require_once('Validator.php');
class EmailValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          if(!filter_var($value,FILTER_VALIDATE_EMAIL)){
               $this->setError('Chybný tvar');
               throw new ValidatorException();
          }
          return $value;
     }
}
