<?php
require_once('SelectWidget.php');
class ObecWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               
               require(dirname(__FILE__).'/../db.php');
               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name') 
                    ->from('obec')->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $options['choices']=$ch;
          }
          if(!isset($options['empty'])){
               $options['empty']=array(''=>'--- Vyberte obec ---');
          }
          parent::__construct($options,$attributes);
     }

     public function setValue($value){
          parent::setValue($value);

          //
          if($this->hasValue()){
               require(dirname(__FILE__).'/../db.php');
               $db_uzemi = getdb('uzemi');

               $okres = $db_uzemi->select('okres')
                    ->from('obec')->where('id = %u',$value)->fetchSingle();
               $res = $db_uzemi->select('id,name')
                    ->from('obec')->where('okres = %u',$okres)->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $this->setOption('choices',$ch);
          }
     }
}
