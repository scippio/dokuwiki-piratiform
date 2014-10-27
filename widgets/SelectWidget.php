<?php
require_once('Widget.php');
class SelectWidget extends Widget {
     public function render(){
          if($this->getOption('expanded',false)){
               $out = '';
               $out .= '<div class="control-group';
               if($this->hasOption('rowclass')) $out .= ' '.$this->getOption('rowclass');
               if($this->validator->hasError()) $out .= ' error';
               $out .= '">';
                    $label = $this->getOption('label');
                    if(!empty($label)) $out .= '<label class="control-label">'.$this->getOption('label').'</label>';
                    $out .= '<div class="controls">';
                         if($this->validator->hasError()) $out .= '<span class="help-block">'.$this->validator->getError().'</span>';
                         $out .= $this->renderWidget();
                    $out .= '</div>';
               $out .= '</div>';
               return $out;
          } else return parent::render();
     }

     public function renderWidget(){
          $events = $this->renderEvents();

          if($this->getOption('expanded',false)){
               $out = '';
               foreach($this->getOption('choices') as $value=>$name){
                    $out .= '<label class="'.($this->getOption('multiple',false)?'checkbox':'radio').'">';
                         if($this->getOption('required',true)) $out .= '&nbsp;<span class="label label-important" title="Povinný údaj">!</span>';
                         $out .= '<input type="'.($this->getOption('multiple',false)?'checkbox':'radio').'" name="'.$this->getOption('name').'" id="'.$this->getOption('formname').'_'.$this->getOption('name').'" value="'.$value.'"';
                         if($this->hasValue() and $this->getValue()==$value) $out .= ' checked="checked"';
                         $out .= $events['element'];
                         // attributes
                         foreach($this->attributes as $attrname=>$attrvalue){
                              if($attrname=='readonly'){ $attrname='disabled'; $attrvalue='disabled'; }
                              $out .= ' '.$attrname.'="'.$attrvalue.'"';
                         }
                         if($this->getOption('required',true)) $out .= ' required="required"'; 
                         $out .= '> '.$name;
                         if($this->hasAttribute('readonly')) $out .= ' <input type="hidden" name="'.$this->getOption('name').'" value="'.$value.'">';
                    $out .= '</label>';
                    if($this->hasOption('help')) $out .= '<span class="help-block">'.$this->getOption('help').'</span>';
               }
          } else {
               $out = '<select name="'.$this->getOption('name').'" id="'.$this->getOption('formname').'_'.$this->getOption('name').'"';
               if($this->getOption('required',true)) $out .= ' required="required"';
               foreach($this->attributes as $attrname=>$attrvalue){
                    $out .= ' '.$attrname.'="'.$attrvalue.'"';
               }
               $out .= '>';
               if($this->hasOption('empty')){
                    $em = $this->getOption('empty');
                    $out .= '<option value="'.key($em).'">'.current($em).'</option>';
               }
               foreach($this->getOption('choices') as $value=>$name){
                    $out .= '<option value="'.$value.'"';
                    if($this->hasValue()){
                         if($this->getValue()==$value) $out .= ' selected="selected"';
                    }
                    if(in_array($value,$this->getOption('dchoices',array()))){
                         $out .= ' disabled="disabled"';
                    }
                    $out .= '>'.$name.'</option>';
               }
               $out .= '</select>';
               if($this->getOption('required',true)) $out .= '&nbsp;<span class="label label-important" title="Povinný údaj">!</span>';
               if($this->hasOption('help')) $out .= '<span class="help-block">'.$this->getOption('help').'</span>';
          }

          // init events
          $out .= '<script type="text/javascript">';
          $out .= 'jQuery(document).ready(function(){ '.$events['init'].' });';
          $out .= '</script>';

          return $out;
     }
}
