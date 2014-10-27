<?php
class ValidatorException extends Exception {
}
class Validator {
     private $options = array();
     private $error = null;
     public function __construct($options=array()){
          $this->options = $options;
          if(!isset($options['required'])) $this->options['required'] = true;
     }

     public function setOption($name,$value){
          $this->options[$name] = $value;
     }

     public function getOption($name,$default=null){
          return ($this->hasOption($name)?$this->options[$name]:$default);
     }

     public function hasOption($name){
          return isset($this->options[$name]);
     }

     public function validate($value){

          // required
          if($this->getOption('required') and empty($value)){
               $this->setError('Pole je nutné vyplnit.');
               //return false;
               throw new ValidatorException();
          }
          return $value;
     }

     public function setError($text){
          $this->error = $text;
     }

     public function hasError(){
          return (!is_null($this->error));
     }

     public function getError(){
          return $this->error;
     }

     public function getDbType(){
          return 'string';
     }
}
