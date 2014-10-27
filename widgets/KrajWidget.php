<?php
require_once('SelectWidget.php');
class KrajWidget extends SelectWidget {
     public function renderWidget(){
          require(dirname(__FILE__).'/../db.php');
          $db = getdb('uzemi');

          $res = $db->select('id,name')->from('kraj')
               ->orderBy('name')->execute();

          $ch = array();
          foreach($res as $data) $ch[$data->id]=$data->name;
          $db->disconnect();

          $this->setOption('choices',$ch);
          $this->setOption('empty',array(''=>'--- Vyberte kraj ---'));
          return parent::renderWidget();
     }

     public function setValue($value){
          parent::setValue($value); 

          //
          if($this->hasValue()){
               require(dirname(__FILE__).'/../db.php');
               $db = getdb('uzemi');

               $res = $db->select('id,name')->from('kraj')
                    ->orderBy('name')->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db->disconnect();

               $this->setOption('choices',$ch);
          }
     }

}
