<?php
require_once('StringValidator.php');
class RegexpValidator extends StringValidator {
     public function validate($value){
          parent::validate($value);
          if(!empty($value) and !preg_match('/'.$this->getOption('regexp','noregexp').'/',$value)){
               $this->setError('Chybný tvar');
               throw new ValidatorException();
          }
          return $value;
     }
}
