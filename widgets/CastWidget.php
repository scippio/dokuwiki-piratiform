<?php
require_once('SelectWidget.php');
class CastWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               require(dirname(__FILE__).'/../db.php');
               $db = getdb('uzemi');

               $res = $db->select('id,name') 
                    ->from('cast')
                    ->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db->disconnect();

               $options['choices']=$ch;
          }
          if(!isset($options['empty'])){
               $options['empty']=array(''=>'--- Vyberte část obce ---');
          }
          parent::__construct($options,$attributes);
     }

     public function setValue($value){
          parent::setValue($value);
          //
          if($this->hasValue()){
               require(dirname(__FILE__).'/../db.php');
               $db = getdb('uzemi');

               $obec = $db->select('obec')
                    ->from('cast')->where('id = %u',$value)->fetchSingle();
               $res =  $db->select('id,name')
                    ->from('cast')->where('obec = %u',$obec)->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db->disconnect();

               $this->setOption('choices',$ch);
          }
     }
}
