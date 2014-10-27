<?php
require_once('SelectWidget.php');
class OkrsekWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               require(dirname(__FILE__).'/../db.php');
               $db = getdb('uzemi');
     
               $res = $db->select('id,num') 
                    ->from('okrsek')
                    ->orderBy('num')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->num;
               $db->disconnect();

               $options['choices']=$ch;
          }
          if(!isset($options['empty'])){
               $options['empty']=array(''=>'--- Vyberte okrsek ---');
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
                    ->from('okrsek')->where('id = %u',$value)->fetchSingle();
               $res = $db->select('id,num')
                    ->from('okrsek')->where('obec = %u',$obec)->orderBy('num')
                    ->execute();
               //
               $ovk = getdb('ovk');
               $res2 = $db->select('okrsek')
                    ->from('ovk')->where('town = %u',$obec)->or('ovk_town = %u',$obec)
                    ->execute();
               $ch2 = array();
               foreach($res2 as $data2){ if($value!=$data2->okrsek)$ch2[] = $data2->okrsek; }

               //

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->num;
               $db->disconnect();

               $this->setOption('dchoices',$ch2);
               $this->setOption('choices',$ch);
          }
     }

     public function renderWidget(){
          $out = parent::renderWidget();

          $out .= '<div id="piratiform_okrsekwidget_map" style="display:none; font-weight:bold;">';
               $out .= '<a target="_blank" href="#">Zobrazit okrsek v mapě</a> (otevře nové okno)';
          $out .= '</div>';
          $out .= '<script type="text/javascript">jQuery(document).ready(function(){';
               $out .= 'jQuery(\'#'.$this->getOption('formname').'_'.$this->getOption('name').'\').change(function(){';
                    $out .= 'jQuery(\'#piratiform_okrsekwidget_map\').hide(); var val=jQuery(this).val(); jQuery.ajax({ type: "POST", url: DOKU_BASE+\'lib/exe/ajax.php\', data: { call: \'piratiform\', id: JSINFO.id, data: \'okrsekcode\', value: val }, success: function(dat){ jQuery(\'#piratiform_okrsekwidget_map a\').attr(\'href\',\'http://vdp.cuzk.cz/marushka/?MarQueryID=VO&MarQParamCount=1&MarQParam0=\'+dat+\'\'); jQuery(\'#piratiform_okrsekwidget_map\').show(); }, dataType: \'json\' })';
               $out .= '});';
          $out .= '});</script>';
          
          return $out;
     }
}    
