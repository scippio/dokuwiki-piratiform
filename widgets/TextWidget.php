<?php
require_once('StringWidget.php');
class TextWidget extends StringWidget {
     
     public function renderWidget(){
          $events = $this->renderEvents();

          $out = '<textarea name="'.$this->getOption('name').'" type="'.$this->getOption('type','text').'" id="'.$this->getOption('formname').'_'.$this->getOption('name').'"';
          if($this->hasOption('placeholder')) $out .= ' placeholder="'.$this->getOption('placeholder').'"';
          if($this->getOption('required',true)) $out .= ' required="required"';
          $out .= $events['element'];
          // attributes
          foreach($this->attributes as $attrname=>$attrvalue){
               $out .= ' '.$attrname.'="'.$attrvalue.'"';
          }
          $out .= '>';

          if($this->hasValue()) $out.=''.$this->getValue().'';

          $out .= '</textarea>';

          if($this->getOption('required',true)) $out .= '&nbsp;<span class="label label-important" title="Povinný údaj">!</span>';
          if($this->hasOption('help')) $out .= '<span class="help-block">'.$this->getOption('help').'</span>';

          // init events
          $out .= '<script type="text/javascript">';
          $out .= 'jQuery(document).ready(function(){ '.$events['init'].' });';
          $out .= '</script>';

          return $out;
     }
}
