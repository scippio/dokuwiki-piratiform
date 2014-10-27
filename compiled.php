<?php
class ValidatorException extends Exception {
}
class Validator {
     private $options = array();
     private $error = null;
     public function __construct($options=array()){
          $this->options = $options;
          if(!isset($options['required'])) $this->options['required'] = true;
     }

     public function setOption($name,$value){
          $this->options[$name] = $value;
     }

     public function getOption($name,$default=null){
          return ($this->hasOption($name)?$this->options[$name]:$default);
     }

     public function hasOption($name){
          return isset($this->options[$name]);
     }

     public function validate($value){

          // required
          if($this->getOption('required') and empty($value)){
               $this->setError('Pole je nutné vyplnit.');
               //return false;
               throw new ValidatorException();
          }
          return $value;
     }

     public function setError($text){
          $this->error = $text;
     }

     public function hasError(){
          return (!is_null($this->error));
     }

     public function getError(){
          return $this->error;
     }

     public function getDbType(){
          return 'string';
     }
}


class StringValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          return $value;
     }
}


class NumberValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          if(!preg_match('/^[0-9]+$/',$value)){
               $this->setError('Chybný tvar čísla');
               throw new ValidatorException();
          }
          return $value;
     }
     public function getDbType(){
          return 'integer';
     }
}


class RegexpValidator extends StringValidator {
     public function validate($value){
          parent::validate($value);
          if(!empty($value) and !preg_match('/'.$this->getOption('regexp','noregexp').'/',$value)){
               $this->setError('Chybný tvar');
               throw new ValidatorException();
          }
          return $value;
     }
}


class SelectValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          $ch = $this->getOption('choices');
          $ch[''] = '- norequired -';
          if(!array_key_exists($value,$ch)){
               //var_dump($this->getOption('choices'));
               $this->setError('Chybná možnost');
               throw new ValidatorException();
          }
          return $value;
     }
}


class EmailValidator extends Validator {
     public function validate($value){
          parent::validate($value);
          if(!filter_var($value,FILTER_VALIDATE_EMAIL)){
               $this->setError('Chybný tvar');
               throw new ValidatorException();
          }
          return $value;
     }
}


class IddsValidator extends RegexpValidator {
     public function validate($value){
          $this->setOption('regexp','^[a-z0-9]{7}$');
          parent::validate($value);
          return $value;
     }
}


class RcValidator extends Validator {
     public function validate($value){
          $value = parent::validate($value);
          $rc = str_replace(array('(',')','/'),'',$value);
          // podmínka 1 + 2 + 3
          if(preg_match('/^[0-9]{9,10}$/',$rc)){

               // podminka 4
               if(strlen($rc)==9 and substr($rc,-3)=='000'){
                    $this->setError('Chybné/á číslice');
                    throw new ValidatorException();
               }

               // mezikroky
               $month = substr($rc,2,2);
               $day = substr($rc,4,2);
               $year = substr($rc,0,2);
               //
               if($month>50) $month = $month-50; // zena else muz
               if($month>20){ $rc_plus=true; $month = $month-20; } else $rc_plus=false;
               if($day>40){ $ecp=true; $day = $day-40; } else $ecp=false;

               // podminka 5
               if($rc_plus and $ecp){
                    $this->setError('Chybný tvar');
                    throw new ValidatorException();
               }
          
               // mezikroky
               if(strlen($rc)==9){
	               if($year>53) $cen=18;
               	else $cen=19;
	          }
	          if(strlen($rc)==10){
          	     if($year>53) $cen=19;
                    else $cen=20;
	          }
               $fullyear = $cen.$year;

               // podminka 6 + 7 + 8 + 9
               if($year<0 or $year>99){
                    $this->setError('Chybné datum');
                    throw new ValidatorException();
                    //return $day.'.'.$month.'. '.$fullyear.'?';
               }
               if($month<1 or $month>12){
                    $this->setError('Chybné datum');
                    throw new ValidatorException();
                    //return $day.'.'.$month.'. '.$fullyear.'?';
               }
               if(!checkdate($month,$day,$fullyear)){
                    $this->setError('Chybné datum');
                    throw new ValidatorException();
                    //return $day.'.'.$month.'. '.$fullyear.'?';
               }

               // podminka 10
               // podminka 11

               // podminka 12
               if(strlen($rc)==10 and $rc%11!=0){
                    $this->setError('RČ má chybný tvar');
                    throw new ValidatorException();
               }

               // minage
               if($this->hasOption('minage')){
                    $date1 = new DateTime($fullyear.'-'.$month.'-'.$day);
                    $date2 = $this->getOption('agedate');
                    if(is_null($date2)) $date2 = new DateTime();
                    else $date2 = new DateTime($date2);
                    $diff = $date1->diff($date2);
                    if($diff->format('%y') < $this->getOption('minage')){
                         $this->setError('Příliš nízký věk');
                         throw new ValidatorException();
                    }
               }
          }
          return $rc;
     }
     public function getDbType(){
          return 'bigint';
     }
}


class KrajValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')
               ->from('kraj')
               ->where('id = %u',$value)
               /*->where('id = %u',$value)*/->execute();
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


class KrajOvkValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')
               ->from('kraj')
               ->where('hidden is null')->and('id = %u',$value)
               /*->where('id = %u',$value)*/->execute();
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


class OkresValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')
               ->from('okres')
               ->where('id = %u',$value)
               ->execute();
               //->where('id = %u',$value)->count();
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


class OkresOvkValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')->from('okres')
               ->where('hidden is null')->and('id = %u',$value)
               ->execute();
               //->where('id = %u',$value)->count();
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


class ObecValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
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


class ObecOvkValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')->from('obec')
               ->where('hidden is null')->and('id = %u',$value)
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


class CastValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')
               ->from('cast')->where('id = %u',$value)->execute();
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


class UliceValidator extends StringValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');
          $res = $db->select('id,name')
               ->from('ulice')
               ->where('id = %u',$value)
               ->execute();
          $ch = array(''=>'- norequired -');
          foreach($res as $data) $ch[$data->id]=$data->name;
          $this->setOption('choices',$ch);
          parent::validate($value);
          return $value;
     }
}


class OkrsekValidator extends SelectValidator {
     public function validate($value){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');

          $res = $db->select('id,num')
               ->from('okrsek')
               ->where('id = %u',$value)
               ->execute();
          $ch = array(''=>'- norequired -');
          foreach($res as $data) $ch[$data->id]=$data->num;
          $this->setOption('choices',$ch);
          parent::validate($value);
          return $value;
     }
     public function getDbType(){
          return 'uinteger';
     }
}

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


class StringWidget extends Widget {
}


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


class EmailWidget extends StringWidget {
     public function renderWidget(){
          $this->setOption('type','email');
          return parent::renderWidget();
     }     
}


class NumberWidget extends StringWidget {
     public function renderWidget(){
          $this->setOption('type','number');
          return parent::renderWidget();
     }
}


class WhisperWidget extends StringWidget {
}


class RcWidget extends StringWidget {
     public function setValue($value){
          parent::setValue($value);
          if($this->hasValue()){
               $value = str_replace('/','',$this->getValue());
               $len = strlen($value);
               $this->value = substr($value,0,6).'/'.substr($value,6,$len-6);
          }
     }
}


class KrajWidget extends SelectWidget {
     public function renderWidget(){
          require(dirname(__FILE__).'/db.php');
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
               require(dirname(__FILE__).'/db.php');
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


class KrajOvkWidget extends SelectWidget {
     public function renderWidget(){
          require(dirname(__FILE__).'/db.php');
          $db = getdb('uzemi');

          $res = $db->select('id,name')->from('kraj')
               ->where('hidden is null')
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
               require(dirname(__FILE__).'/db.php');
               $db = getdb('uzemi');

               $res = $db->select('id,name')->from('kraj')
                    ->where('hidden is null')
                    ->orderBy('name')->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db->disconnect();

               $this->setOption('choices',$ch);
          }
     }

}


class OkresWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               require(dirname(__FILE__).'/db.php');
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

               require(dirname(__FILE__).'/db.php');
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


class OkresOvkWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               require(dirname(__FILE__).'/db.php');
               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name') ->from('okres')
                    ->where('hidden is null')->orderBy('name')
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

               require(dirname(__FILE__).'/db.php');
               $db_uzemi = getdb('uzemi');

               $kraj = $db_uzemi->select('kraj') ->from('okres')
                    ->where('hidden is null')->and('id = %u',$value)->fetchSingle();
               $res = $db_uzemi->select('id,name')->from('okres')
                    ->where('hidden is null')->and('kraj = %u',$kraj)->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $this->setOption('choices',$ch);
          }
     }
}


class ObecWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               
               require(dirname(__FILE__).'/db.php');
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
               require(dirname(__FILE__).'/db.php');
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


class ObecOvkWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               
               require(dirname(__FILE__).'/db.php');
               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name') ->from('obec')
                    ->where('hidden is null')->orderBy('name')
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
               require(dirname(__FILE__).'/db.php');
               $db_uzemi = getdb('uzemi');

               $okres = $db_uzemi->select('okres')->from('obec')
                    ->where('hidden is null')->and('id = %u',$value)->fetchSingle();
               $res = $db_uzemi->select('id,name')->from('obec')
                    ->where('hidden is null')->and('okres = %u',$okres)->orderBy('name')
                    ->execute();

               $ch = array();
               foreach($res as $data) $ch[$data->id]=$data->name;
               $db_uzemi->disconnect();

               $this->setOption('choices',$ch);
          }
     }
}


class CastWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               require(dirname(__FILE__).'/db.php');
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
               require(dirname(__FILE__).'/db.php');
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


class UliceWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){

               require(dirname(__FILE__).'/db.php');
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

               require(dirname(__FILE__).'/db.php');
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


class OkrsekWidget extends SelectWidget {
     public function __construct($options=array(),$attributes=array()){
          if(!isset($options['choices'])){
               require(dirname(__FILE__).'/db.php');
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
               require(dirname(__FILE__).'/db.php');
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
