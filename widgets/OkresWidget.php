<?php
require_once('SelectWidget.php');
class OkresWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               require(dirname(__FILE__).'/../db.php');
               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name') 
                    ->from('okres')->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $options['choices']=$ch;
          }
          if(!isset($options['empty'])){
               $options['empty']=array(''=>'--- Vyberte okres ---');
          }
          parent::__construct($options,$attributes);
     }

     public function setValue($value){
          parent::setValue($value);

          //
          if($this->hasValue()){

               require(dirname(__FILE__).'/../db.php');
               $db_uzemi = getdb('uzemi');

               $kraj = $db_uzemi->select('kraj') 
                    ->from('okres')->where('id = %u',$value)->fetchSingle();
               $res = $db_uzemi->select('id,name')
                    ->from('okres')->where('kraj = %u',$kraj)->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $this->setOption('choices',$ch);
          }
     }
}
