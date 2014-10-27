<?php
/*
require(dirname(__FILE__).'/widgets/Widget.php');
require(dirname(__FILE__).'/validators/Validator.php');
*/
require(dirname(__FILE__).'/compiled.php');
require_once(dirname(__FILE__).'/dibi.min.php');

class PiratiForm {

     private $elements = array();
     private $validators = array();
     private $options = array(
          'method' => 'post'
     );
     private $widgets_cnt = 0;
     private $validators_cnt = 0;

     private $actions = array();
     private $data = array();
     private $data_type = array();

     private $errors = array();

     private $reserved = array('idate','cancel','cancel_date','hash','id');

     public function __construct(){
          $this->init();

          if(isset($_GET['admin']) and $this->getOption('admin',false)){
               if(isset($_GET['adminkey']) and $_GET['adminkey']==$this->getOption('admin_key')){
                    $this->setOption('mode','admin');
               }    
          }

          if($this->widgets_cnt!=$this->validators_cnt) throw new Exception('Number of Widgets and Validators is different');
          // required
          foreach($this->elements as $el){
               if($el['type']=='fieldset'){
                    foreach($el['widgets'] as $k=>$w){
                         if(in_array($k,$this->reserved)) throw new Exception('Reserved widget index: '.$k);
                         $w->setOption('required',$this->validators[$k]->getOption('required',true));
                         $w->setOption('form',$this);
                         $w->setOption('name',$k);
                         $this->validators[$k]->setOption('choices',$w->getOption('choices'));
                         $w->setValidator($this->validators[$k]);
                    }
               }
               // widget
               if($el['type']=='widget'){
                    if(in_array($el['name'],$this->reserved)) throw new Exception('Reserved widget index: '.$el['name']);
                    $el['widget']->setOption('required',$this->validators[$el['name']]->getOption('required',true));
                    $el['widget']->setOption('form',$this);
                    $el['widget']->setOption('name',$el['name']);
                    $this->validators[$el['name']]->setOption('choices',$el['widget']->getOption('choices'));
                    $el['widget']->setValidator($this->validators[$el['name']]);
               }
          }
     }

     public function addFieldset($name, $label, array $widgets){
          $this->elements[] = array(
               'type' => 'fieldset',
               'label' => $label,
               'widgets' => $widgets
          );
          $this->widgets_cnt += count($widgets);
     }

     public function addWidget($name, Widget $widget){
          $this->elements[] = array(
               'type' => 'widget',
               'name' => $name,
               'widget' => $widget
          );
          $this->widgets_cnt++;
     }

     public function addAdminFieldset($name, $label, array $widgets){
          $this->elements[] = array(
               'type' => 'fieldset',
               'label' => $label,
               'widgets' => $widgets,
               'mode' => 'admin'
          );
          $this->widgets_cnt += count($widgets);
     }

     public function addValidator($name, Validator $validator){
          $this->validators[$name] = $validator;
          $this->validators_cnt++;
     }

     public function addHtml($name, $html){
          $this->elements[] = array(
               'type' => 'html',
               'name' => 'name',
               'html' => $html
          );
     }

     public function bind($data){
          if(is_array($data)){
               foreach($this->elements as $el){
                    switch($el['type']){
                         case 'fieldset':
                              foreach($el['widgets'] as $k=>$w){
                                   if(isset($data[$k])){
                                        $w->setValue($data[$k]);
                                   } else $w->setValue(null);
                              }
                              break;
                         case 'widget':
                              if(isset($data[$el['name']])){
                                   $el['widget']->setValue($data[$el['name']]);
                              } else $el['widget']->setValue(null);
                              break;
                    }
               }
          }
     }

     public function edit($url){
          global $ID;
          if(preg_match('/^[0-9]+\-[a-z0-9]+$/',$url)){
               list($id,$hash) = explode('-',$url);
               $dbaction = null;
               foreach($this->actions as $act){ if($act['type']=='db') $dbaction=$act; break; }

               dibi::connect($dbaction['db']);
               $q = dibi::select('*')->from($dbaction['table'])
                    ->where('id = %u',$id)->and('hash = %s',$hash);
               if($this->getOption('cancel')){
                    $q->and('cancel = %b',false);
               }
               $res = $q->fetch();

               if(!$res) send_redirect(wl($ID));

               $res = $res->toArray();
               $this->setOption('userid',$res['id']);
               $this->setOption('hash',$res['hash']);
               $this->bind($res);
               $this->setOption('buttons',true);
               if($this->getOption('mode')!='admin') $this->setOption('mode','edit');
          }
     }

     public function addAction($action){
          $this->actions[] = $action;
     }

     public function action(){
          $ret = false;
          foreach($this->actions as $act){
               switch($act['type']){
                    case 'db': // save into db
                         if($this->getOption('mode')=='edit' or $this->getOption('mode')=='admin') $data=array();
                         else $data = array('idate%t'=>date('Y-m-d H:i:s'));

                         if($act['db']['driver']=='sqlite3')
                              $q = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
                         else $q = 'id INTEGER AUTO_INCREMENT PRIMARY KEY';
                         $q .= ', idate DATETIME';
                         if($this->hasOption('cancel')){
                              $q .= ', cancel BOOLEAN, cancel_date DATETIME';
                              if($this->getOption('mode')!='edit' and $this->getOption('mode')!='admin') $data['cancel%b'] = false;
                         }
                         if($this->hasOption('cancel') or $this->hasOption('edit')){ 
                              if($this->getOption('mode')!='edit' and $this->getOption('mode')!='admin'){
                                   $this->setOption('hash',sha1(rand(0,1000).date('YmdHis').microtime()));
                                   $q .= ', hash VARCHAR(255)';
                                   $data['hash'] = $this->getOption('hash');
                              }
                         }
                         if($this->hasOption('edit')){
                              $q .= ', edit_date DATETIME';
                         }
                         foreach($this->data_type as $k=>$v){
                              if(!empty($q)) $q.=',';
                              $q .= $k;
                              switch($v){
                                   case 'string': $q .= ' VARCHAR(255)'; $data[$k.'%s'] = $this->data[$k]; break;
                                   case 'integer':
                                   case 'uinteger': $q .= ' INTEGER'; $data[$k.'%i'] = $this->data[$k]; break;
                                   case 'bigint':  $q .= ' BIGINT'; $data[$k.'%i'] = $this->data[$k]; break;
                              }
                         }
                         try {
                              dibi::connect($act['db']);
                              dibi::query('CREATE TABLE IF NOT EXISTS '.$act['table'].' ('.$q.');');
                              if($this->getOption('mode')=='edit' or $this->getOption('mode')=='admin'){
                                   $data['edit_date%t'] = date('Y-m-d H:i:s');

                                   if(isset($act['unique']) and is_array($act['unique'])){                                   
                                        foreach($act['unique'] as $field=>$error){
                                             $q = dibi::select('*')->from($act['table'])
                                                  ->where('id != %u',$this->getOption('userid'));
                                             if($this->hasOption('cancel')) $q->and('cancel = %b',false);
                                             $cnt = $q->and($field.' = %s',$this->data[$field])->count();
                                             if($cnt>0){
                                                  $this->addError($error);
                                                  return false;
                                             }
                                        }
                                   }

                                   dibi::update($act['table'],$data)
                                        ->where('id = %u',$this->getOption('userid'))
                                        ->and('hash = %s',$this->getOption('hash'))
                                        ->execute();
                              } else {
                                   if(isset($act['unique']) and is_array($act['unique'])){
                                        //$ucnt = count($act['unique']);
                                        foreach($act['unique'] as $field=>$error){
                                             $q = dibi::select('*')->from($act['table'])
                                                  ->where('1=1');
                                             if($this->hasOption('cancel')) $q->and('cancel = %b',false);
                                             $cnt = $q->and($field.' = %s',$this->data[$field])->count();
                                             if($cnt>0){
                                                  $this->addError($error);
                                                  return false;
                                             }
                                        }
                                   }
                                   dibi::insert($act['table'],$data)->execute();
                                   $this->setOption('userid',dibi::insertId());
                              }
                              dibi::disconnect();
                         } catch(DibiException $e){
                              throw new Exception($e);
                              return false;
                         }
                         break;
                    case 'mail':
                         $ret = $this->mailAction($act);
                         break;
               }
          }
          return $ret;
     }

     public function cancel(){
          foreach($this->actions as $act){
               if($act['type']=='db'){
                    dibi::connect($act['db']);
                    dibi::update($act['table'],array(
                         'cancel%b' => true,
                         'cancel_date%t' => date('Y-m-d H:i:s')
                    ))->where('id = %u',$this->getOption('userid'))
                    ->and('hash = %s',$this->getOption('hash'))->execute();
                    /*dibi::select()
                         ->from($act['table'])
                         ->where('id = %u',$this->getOption('userid'))
                         ->and('hash = %s',$this->getOption('hash'))->execute();*/
                    dibi::disconnect();
               }
               if($act['type']=='mail'){
                    $to = (isset($act['to'])?$act['to']:'');
                    if(isset($act['tofield'])){
                         dibi::connect($this->getOption('cancel_db'));
                         $to = dibi::select($act['tofield'])
                              ->from($this->getOption('cancel_table'))
                              ->where('id = %u',$this->getOption('userid'))
                              ->and('hash = %s',$this->getOption('hash'))->fetchSingle();
                         dibi::disconnect();
                    }
                    $body = str_replace(array('%url%'),array($url),$this->getOption('cancel_mail'));
                    mail_send($to,$this->getOption('cancel_subject'),$body,$this->getOption('cancel_from',$act['from']));
               }
          }
     }

     public function resend(){
          foreach($this->actions as $act){
               if($act['type']=='mail') $this->mailAction($act);
          }
     }

     private function mailAction($act){
          global $ID;
          $url = wl($ID);
          $data = '';
          $to = (isset($act['to'])?$act['to']:'');
          if(!isset($act['dataout'])) $act['dataout'] = array();

          foreach($this->elements as $el){
               if($el['type']=='fieldset'){
                    $data .= '---- '.$el['label'].' ----'."\n";
                    foreach($el['widgets'] as $k=>$w){
                         if(!in_array($k,$act['dataout'])){
                              if($w->hasValue()){
                                   if($w->hasOption('choices')){
                                        $choices = $w->getOption('choices');
                                        if($w->hasOption('label')) $data .= $w->getOption('label').': ';
                                        $data .= $choices[$w->getValue()]."\n";
                                   } else {
                                        if($w->hasOption('label')) $data .= $w->getOption('label').': ';
                                        $data .= $w->getValue()."\n";
                                   }

                                   if($act['tofield']==$k) $to = $w->getValue();
                              }
                         }
                    }
               }
               if($el['type']=='widget'){
                    if(!in_array($el['name'],$act['dataout'])){
                         if($el['widget']->hasValue()){
                              if($el['widget']->hasOption('choices')){
                                   $choices = $el['widget']->getOption('choices');
                                   if($el['widget']->hasOption('label')) $data .= $el['widget']->getOption('label').': ';
                                   $data .= $choices[$el['widget']->getValue()]."\n";
                              } else {
                                   if($el['widget']->hasOption('label')) $data .= $el['widget']->getOption('label').': ';
                                   $data .= $el['widget']->getValue()."\n";
                              }
                              if($act['tofield']==$el['name']) $to = $el['widget']->getValue();
                         }
                    }
               }
          }
          $userurl = wl($ID).'?piratiform='.$this->getOption('userid').'-'.$this->getOption('hash');
          $body = str_replace(array('%url%','%data%','%userurl%'),array($url,$data,$userurl),$act['body']);
          mail_send($to,$act['subject'],$body,$act['from']);
          return true;
     }

     public function validate(){
          $ret = true;
          foreach($this->elements as $el){
               if($el['type']=='fieldset'){
                    foreach($el['widgets'] as $n=>$w){
                         $validator = $this->validators[$n];
                         try {
                              $this->data[$n] = $validator->validate($w->getValue());
                              $this->data_type[$n] = $validator->getDbType();
                         } catch(ValidatorException $e){
                              $ret = false;
                         }
                         // $w->setValidator($validator);
                    }
               } else if($el['type']=='widget'){
                    try {
                         $validator = $this->validators[$el['name']];
                         $this->data[$el['name']] = $validator->validate($el['widget']->getValue());
                         $this->data_type[$el['name']] = $validator->getDbType();
                    } catch(ValidatorException $e){
                         $ret = false;
                    }
                    // $el['widget']->setValidator($this->validators[$el['name']]);
               }
          }
          return $ret;
     }

     public function setOption($name,$value){
          $this->options[$name] = $value;
     }

     public function getOption($name='',$default=null){
          return ($this->hasOption($name)?$this->options[$name]:$default);
     }

     public function hasOption($name){
          return (isset($this->options[$name])?true:false);
     }

     public function render(){
          global $ID;
          $out = '';

          // errors
          if($this->hasErrors()){
               $out .= '<div class="alert alert-error"><strong>Nalezeny chyby:</strong><ul>';
                    foreach($this->getErrors() as $err){
                         $out .= '<li>'.$err.'</li>';
                    }
               $out .= '</ul></div>';
          }
          // saved
          if($this->hasOption('saved') and isset($_GET['saved'])){
               $out .= '<div class="alert alert-success"><strong>'.$this->getOption('saved').'</strong> <a href="'.wl($ID).'">Zpět na formulář.</a></div>';
          }
          if($this->getOption('savedhideform') and isset($_GET['saved'])) return $out;
          // canceled
          if($this->hasOption('canceled') and isset($_GET['canceled'])){
               $out .= '<div class="alert alert-success"><strong>'.$this->getOption('canceled').'</strong> <a href="'.wl($ID).'">Zpět na formulář.</a></div>';
          }
          if($this->getOption('canceledhideform') and isset($_GET['canceled'])) return $out;
          // end
          if($this->hasOption('endtime') and time()>$this->getOption('endtime',0) and !isset($_GET['admin'])){
               $out .= '<div class="alert alert-info"><strong>'.$this->getOption('endtime_msg').'</strong></div>';
               return $out;
          }

          //
          if($this->getOption('progress')){
               $dbact = null;
               foreach($this->actions as $act){ if($act['type']=='db')$dbact=$act; break; }
               if(!empty($dbact)){
                    dibi::connect($dbact['db']);
                    $q = dibi::select('*')->from($dbact['table']);
                    if($this->getOption('cancel')) $q->where('cancel = %b',false);
                    $cnt = $q->count();
                    dibi::disconnect();
                    $prc = round($cnt/($this->getOption('progress_limit',100)/100),2);

                    $out .= '<div class="piratifund type-blue">';
                         $out .= '<div class="progress progress-striped active">';
                              $out .= '<div class="bar"></div>';
                              $out .= '<div class="prc">'.$prc.'%</div>';
                         $out .= '</div>';
                         $out .= '<div>';
                              $out .= '<span class="start">0</span>';
                              $out .= '<span class="current">'.$cnt.'</span>';
                              $out .= '<span class="end">'.$this->getOption('progress_limit').'</span>';
                         $out .= '</div>';
                    $out .= '</div>';
               }
          }

          if($this->getOption('mode')=='edit')
               $action = wl($ID).'?piratiform='.$this->getOption('userid').'-'.$this->getOption('hash').'&tseed='.(time()+5);
          else if($this->getOption('mode')=='admin')
               $action = wl($ID).'?piratiform='.$this->getOption('userid').'-'.$this->getOption('hash').'&admin&adminkey='.$this->getOption('admin_key').'&tseed='.(time()+5);
          else
               $action = wl($ID);

          $out .= '<form class="piratiform_form form-horizontal" action="'.$action.'" method="'.$this->getOption('method').'">';

          // buttons
          if($this->getOption('buttons')){
               $out .= '<fieldset><legend>Volby</legend>';
               if($this->getOption('resend')){
                    $out .= '<a href="'.wl($ID).'?piratiform='.$this->getOption('userid').'-'.$this->getOption('hash').'&resend" class="btn">'.$this->getOption('resend_button','Zaslat údaje na mail').'</a>';
               }
               if($this->getOption('cancel')){
                    $out .= '<a href="'.wl($ID).'?piratiform='.$this->getOption('userid').'-'.$this->getOption('hash').'&cancel" class="pull-right btn btn-danger" onclick="return confirm(\''.$this->getOption('cancel_confirm','Opravdu si přejete zrušit záznam?').'\');">'.$this->getOption('cancel_button','Zrušit').'</a>';
               }
               $out .= '</fieldset>';
          }

          $out .= '<input type="hidden" name="'.$this->getOption('name','piratiform').'" value="pyrat">';
          $out .= '<input type="hidden" name="sectok" value="'.(getSecurityToken()).'">';
          foreach($this->elements as $i=>$el){
               if(!empty($el['mode']) and $el['mode']!=$this->getOption('mode')) continue;

               switch($el['type']){
                    case 'html':
                         $out .= $el['html'];
                         break;
                    case 'fieldset':
                         $out .= '<fieldset><legend>'.$el['label'].'</legend>';
                         foreach($el['widgets'] as $k=>$w){
                              $w->setOption('formname',$this->getOption('name','piratiform'));
                              $w->setOption('name',$k);
                              $out .= $w->render();
                         }
                         $out .= '</fieldset>';
                         break;
                    case 'widget':
                         $el['widget']->setOption('formname',$this->getOption('name','piratiform'));
                         $el['widget']->setOption('name',$el['name']);
                         $out .= $el['widget']->render();
                         break;
               }
          }
          $out .= '<div class="control-group">';
               $out .= '<div class="controls">';
                    if($this->getOption('mode')=='edit' or $this->getOption('mode')=='admin'){
                         $out .= '<button type="submit" class="btn btn-primary piratiform" data-loading-text="Ukládám změny...">Aktualizovat údaje</button>';
                    } else {
                         $out .= '<button type="submit" class="btn btn-primary piratiform" data-loading-text="Odesílám...">Odeslat</button>';
                    }
               $out .= '</div>';
          $out .= '</div>';
          $out .= '</form>';
          return $out;
     }

     public function getErrors(){
          return $this->errors;
     }

     public function addError($text){
          $this->errors[] = $text;
     }

     public function hasErrors(){
          return !empty($this->errors);
     }
}
