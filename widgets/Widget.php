<?php
class Widget {
     private $options = array();
     protected $attributes = array();
     protected $value = null;
     protected $validator = null;

     public function __construct($options = array(),$attributes = array()){
          $this->attributes = $attributes;
          $this->options = $options;
          if(!is_null($this->getOption('default'))){
               $this->setValue($this->getOption('default'));
          }
     }

     public function setAttribute($name,$value){
          $this->attributes[$name] = $value;
     }

     public function getAttribute($name,$default=null){
          return ($this->hasAttribute($name)?$this->attributes[$name]:$default);
     }

     public function hasAttribute($name){
          return isset($this->attributes[$name]);
     }

     public function setOption($name,$value){
          $this->options[$name] = $value;
     }

     public function getOption($name,$default=null){
          return ($this->hasOption($name)?$this->options[$name]:$default);
     }

     public function hasOption($name){
          return (isset($this->options[$name])?true:false);
     }

     public function setValue($value){
          $this->value = $value;
     }

     public function getValue(){
          return $this->value;
     }

     public function hasValue(){
          //return !is_null($this->value);
          return !empty($this->value);
     }

     public function render(){
          $out = '';
          $out .= '<div class="control-group';
          if($this->hasOption('rowclass')) $out .= ' '.$this->getOption('rowclass');
          if($this->validator->hasError()) $out .= ' error';
          $out .= '">';
               $out .= $this->renderLabel();
               $out .= '<div class="controls">';
               if($this->validator->hasError()) $out .= '<span class="help-block">'.$this->validator->getError().'</span>';
                    $out .= $this->renderWidget();
               $out .= '</div>';
          $out .= '</div>';
          return $out;
     }

     public function renderLabel(){
          return '<label class="control-label" for="'.$this->getOption('formname').'_'.$this->getOption('name').'">'.$this->getOption('label').'</label>';
     }

     public function renderWidget(){
          $events = $this->renderEvents();

          $out = '<input name="'.$this->getOption('name').'" type="'.$this->getOption('type','text').'" id="'.$this->getOption('formname').'_'.$this->getOption('name').'"';
          if($this->hasOption('placeholder')) $out .= ' placeholder="'.$this->getOption('placeholder').'"';
          if($this->hasValue()) $out.=' value="'.$this->getValue().'"';
          if($this->getOption('required',true)) $out .= ' required="required"';
          $out .= $events['element'];
          // attributes
          foreach($this->attributes as $attrname=>$attrvalue){
               $out .= ' '.$attrname.'="'.$attrvalue.'"';
          }
          $out .= '>';
          if($this->getOption('required',true)) $out .= '&nbsp;<span class="label label-important" title="Povinný údaj">!</span>';
          if($this->hasOption('help')) $out .= '<span class="help-block">'.$this->getOption('help').'</span>';

          // init events
          $out .= '<script type="text/javascript">';
          $out .= 'jQuery(document).ready(function(){ '.$events['init'].' });';
          $out .= '</script>';

          return $out;
     }

     public function setValidator($val){
          $this->validator = $val;
     }

     protected function renderEvents(){
          // events
          $init_events = '';
          $element_events = '';
          $change_events = '';
          if($this->hasOption('events')){
               foreach($this->getOption('events') as $e){
                    if($e['trigger']=='init'){
                         if($e['type']=='hide'){
                              if($this->getValue()==$e['value']) $init_events .= 'jQuery(\'.'.$e['class'].'\').hide();';
                         }
                         if($e['type']=='enableonfull'){
                              if($this->hasValue()) $init_events .= 'jQuery(\'#'.$e['id'].'\').prop(\'disabled\',false);';
                         }
                         /*if($e['type']=='ajaxload'){
                              $init_events .= 'loaddata(this,\''.$e['data'].'\',\''.$e['id'].'\');';
                         }*/
                         /*if($e['type']=='typeahead'){
                              $init_events .= 'jQuery(\'#'.$this->getOption('formname').'_'.$this->getOption('name').'\').typeahead({ source: [] ';
                              $init_events .= '   ';
                              $init_events .= '});';
                         }*/
                    } elseif($e['trigger']=='change'){
                         if($e['type']=='hidetoggle'){
                              $change_events .= 'hidetoggle(\''.$e['class'].'\');';
                         }
                         if($e['type']=='ajaxload'){
                              $change_events .= 'loaddata(this,\''.$e['data'].'\',\''.$e['id'].'\');';
                         }
                         if($e['type']=='typeaheadload'){
                              $change_events .= 'loadtypeahead(this,\''.$e['data'].'\',\''.$e['id'].'\');';
                         }
                         if($e['type']=='emptyonempty'){
                              $change_events .= 'emptyonempty(this,\''.$e['id'].'\');';
                         }
                         if($e['type']=='disableonempty'){
                              $change_events .= 'disableonempty(this,\''.$e['id'].'\');';
                         }
                         if($e['type']=='enableonfull'){
                              $change_events .= 'enableonfull(this,\''.$e['id'].'\');';
                         }
                         if($e['type']=='emptyonfull'){
                              $change_events .= 'emptyonfull(this,\''.$e['id'].'\');';
                         }
                         if($e['type']=='disableonfull'){
                              $change_events .= 'disableonfull(this,\''.$e['id'].'\');';
                         }
                         if($e['type']=='required'){
                              $change_events .= 'piratiform_required(this,\''.$e['class'].'\');';
                         }
                    }
               }
          }
          
          if(!empty($change_events)){
               $init_events .= 'jQuery(\'#'.$this->getOption('formname').'_'.$this->getOption('name').'\').change(function(){ '.$change_events.' });';
          }

          return array(
               'element'=>$element_events,
               'init'=>$init_events
          );
     }
}
