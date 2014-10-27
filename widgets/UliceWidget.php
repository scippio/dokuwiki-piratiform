<?php
require_once('SelectWidget.php');
class UliceWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){

               require(dirname(__FILE__).'/../db.php');
               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name') 
                    ->from('ulice')->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $options['choices']=$ch;
          }
          if(!isset($options['empty'])){
               $options['empty']=array(''=>'--- Vyberte ulici ---');
          }
          parent::__construct($options,$attributes);
     }

     public function setValue($value){
          parent::setValue($value);

          //
          if($this->hasValue()){

               require(dirname(__FILE__).'/../db.php');
               $db_uzemi = getdb('uzemi');

               $obec = $db_uzemi->select('obec') 
                    ->from('ulice')->where('id = %u',$value)->fetchSingle();
               $res = $db_uzemi->select('id,name')
                    ->from('ulice')->where('obec = %u',$obec)->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $this->setOption('choices',$ch);
          }
     }
}
