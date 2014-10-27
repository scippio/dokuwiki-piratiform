<?php
require_once('SelectValidator.php');
class ObecValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/../db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')
               ->from('obec')
               ->where('id = %u',$value)
               ->execute();
          $ch = array(''=>'- norequired -');
          foreach($res as $data) $ch[$data->id]=$data->name;
          $this->setOption('choices',$ch);
          parent::validate($value);
          return $value;
     }
     public function getDbType(){
          return 'uinteger';
     }
}
