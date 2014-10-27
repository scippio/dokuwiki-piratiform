<?php
require_once('StringWidget.php');
class RcWidget extends StringWidget {
     public function setValue($value){
          parent::setValue($value);
          if($this->hasValue()){
               $value = str_replace('/','',$this->getValue());
               $len = strlen($value);
               $this->value = substr($value,0,6).'/'.substr($value,6,$len-6);
          }
     }
}
