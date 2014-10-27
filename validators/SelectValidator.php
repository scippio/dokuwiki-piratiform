<?php
require_once('Validator.php');
class SelectValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          $ch = $this->getOption('choices');
          $ch[''] = '- norequired -';
          if(!array_key_exists($value,$ch)){
               //var_dump($this->getOption('choices'));
               $this->setError('Chybná možnost');
               throw new ValidatorException();
          }
          return $value;
     }
}
