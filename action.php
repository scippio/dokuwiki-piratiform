<?php
/**
 * Autostart Plugin: Redirects to the namespace's start page if available
 *
 * @author Jesús A. Álvarez <zydeco@namedfork.net>
 */


if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');

class action_plugin_piratiform extends DokuWiki_Action_Plugin
{
     function register(&$controller) {
          $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'ajax', array ());
          $controller->register_hook('RENDERER_CONTENT_POSTPROCESS', 'BEFORE', $this, 'action', array ());
          //$controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'action', array ());
     }

     public function action(&$event, $param){
          global $ID;

          $adminkey = 'rur';
          if(isset($_GET['admin']) and isset($_GET['adminkey']) and $_GET['adminkey']==$adminkey){
               require_once(DOKU_PLUGIN . '/piratiform/db.php');

               //$db_uzemi = getdb('uzemi');
               $db_ovk = getdb('cf');

               $out =& $event->data[1];
               $out = '<h1>Administrace</h1>';
               
               //
               //$region = ((isset($_GET['region']) and preg_match('/^[0-9]{1,2}$/',$_GET['region']))?$_GET['region']:1);
               //

               $cnt = $db_ovk->select('*')->from('cf')
                    ->where('cancel = %b',false)->count();
               $cnt_c = $db_ovk->select('*')->from('cf')
                    ->where('cancel = %b',true)->count();
               /*$cnt_obec = $db_ovk->select('DISTINCT(town)')->from('ovk')
                    ->where('cancel = %b',false)->count();

               $regions = $db_uzemi->select('id,name')
                    ->from('kraj')->execute();
               $okresy = $db_uzemi->select('id,name')
                    ->from('okres')->where('kraj = %u',$region)->execute();
               $obce = $db_uzemi->select('id,name')
                    ->from('obec')->where('okres IN %in',$okresy)->execute();
               $okrsky = $db_uzemi->select('id,num')->from('okrsek')->fetchPairs();
               */
               $out .= '<strong>Celkem platných registrací:</strong> '.$cnt.' (zrušených: '.$cnt_c.')';
               //$out .= '<strong>Obsazených obcí:</strong> '.$cnt_obec;

               $out .= '<br><br>';

               
               $sql = $db_ovk->select('*')->from('cf');
                    //->where('region = %u',$region);
               $users = $sql->execute();

               $out .= '<div>';
                    //$out .= '<select onchange="var url = \''.wl($ID).'?admin&adminkey='.$adminkey.'&region=\'+jQuery(this).val(); location.href=url;">';
                         //foreach($regions as $r) $out .= '<option value="'.$r->id.'"'.($r->id==$region?' selected="selected"':'').'>'.$r->name.'</option>';
                    //$out .= '</select>';
               $out .= '</div>';
               $out .= '<table class="table table-condensed table-hover table-striped">';
               $out .= '<thead><tr>';
                    $out .= '<th>stav</th>';
                    $out .= '<th>#</th>';
                    $out .= '<th>Jméno a příjmení</th>';
                    $out .= '<th>Telefon</th>';
                    $out .= '<th>E-mail</th>';
                    $out .= '<th>Ubytování</th>';
                    $out .= '<th>Pre-Party</th>';
               $out .= '</tr></thead><tbody>';
               foreach($users as $u){
                    $htype = $u->htype;
                    switch($htype){
                         case 1: $htype = 'Zajistím si sám (nemusím, couchsurfing apod.)'; break;
                         case 2: $htype = 'V Pirátském centru'; break;
                         default: $htype = 'error';
                    }
                    $out .= '<tr>';
                         $out .= '<td>'.($u->cancel?'<span class="badge badge-important" title="Zrušeno: '.date('H:i:s d.m.Y',$u->cancel_date).'">X</span>':'').'</td>';
                         $out .= '<td>'.$u->id.'</td>';
                         $out .= '<td>'.$u->name.' '.$u->surname.'</td>';
                         $out .= '<td>'.$u->phone.'</td>';
                         $out .= '<td>'.$u->email.'</td>';
                         $out .= '<td>'.($htype).'</td>';
                         $out .= '<td>'.(is_null($u->preparty)?'Ne':($u->preparty==1?'Ano':'?')).'</td>';
                         //$out .= '<td><a class="btn btn-mini btn-warning" href="'.wl($ID).'?piratiform='.$u->id.'-'.$u->hash.'">Upravit</a></td>';
                    $out .= '</tr>';
               }
               $out .= '</tbody></table>';

               //$db_uzemi->disconnect();
               $db_ovk->disconnect();
          }
     }

     /*******************/

     public function ajax(&$event, $param){
          if($event->data=='piratiform'){
               if(isset($_POST['data'])){
    
                    if($_POST['data']=='okres' and isset($_POST['value'])) $this->loadOkres($_POST['value']);
                    if($_POST['data']=='obec' and isset($_POST['value'])) $this->loadObec($_POST['value']);
                    if($_POST['data']=='cast' and isset($_POST['value'])) $this->loadCast($_POST['value']);
                    if($_POST['data']=='ulice' and isset($_POST['value'])) $this->loadUlice($_POST['value']);
                    if($_POST['data']=='ulice_array' and isset($_POST['value'])) $this->loadUlice($_POST['value'],'array');
                    if($_POST['data']=='okrsek' and isset($_POST['value'])) $this->loadOkrsek($_POST['value']);
                    if($_POST['data']=='okrsekulice' and isset($_POST['value'])) $this->loadOkrsekByUlice($_POST['value']);
                    if($_POST['data']=='okrsekcode' and isset($_POST['value'])) $this->getOkrsekCode($_POST['value']);
               }
          }
     }

     public function getOkrsekCode($id){
          if(preg_match('/^[0-9]+$/',$id)){
          
               require(dirname(__FILE__).'/db.php');
               $db_uzemi = getdb('uzemi');

               $code = $db_uzemi->select('code')->from('okrsek')->where('id = %u',$id)->fetchSingle();
               echo $code;
               $db_uzemi->disconnect();
          }
     }

     public function loadOkres($kraj){
          if(preg_match('/^[0-9]+$/',$kraj)){
               require(dirname(__FILE__).'/db.php');
               
               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name')->from('okres')->where('kraj = %u',$kraj)->orderBy('name')->execute();
               echo '[';
               foreach($res as $i=>$o){
                    if($i!=0) echo ',';
                    echo '{"value":"'.$o->id.'","name":"'.$o->name.'"}';
               }
               echo ']';
               $db_uzemi->disconnect();
          }
          //if($kraj!='') echo '[{"value":"1","name":"test 1"},{"value":"2","name":"test2"}]';
     }
     public function loadObec($okres){
          if(preg_match('/^[0-9]+$/',$okres)){
               require(dirname(__FILE__).'/db.php');

               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name')->from('obec')->where('okres = %u',$okres)->orderBy('name')->execute();
               echo '[';
               foreach($res as $i=>$o){
                    if($i!=0) echo ',';
                    echo '{"value":"'.$o->id.'","name":"'.$o->name.'"}';
               }
               echo ']';
               $db_uzemi->disconnect();
          }
     }

     public function loadCast($obec){
          if(preg_match('/^[0-9]+$/',$obec)){
               require(dirname(__FILE__).'/db.php');

               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name')->from('cast')->where('obec = %u',$obec)->orderBy('name')->execute();
               echo '[';
               foreach($res as $i=>$o){
                    if($i!=0) echo ',';
                    echo '{"value":"'.$o->id.'","name":"'.$o->name.'"}';
               }
               echo ']';
               $db_uzemi->disconnect();
          }
     }

     public function loadUlice($obec,$format=null){
          if(preg_match('/^[0-9]+$/',$obec)){

               require(dirname(__FILE__).'/db.php');
               $db_uzemi = getdb('uzemi');

               $res = $db_uzemi->select('id,name')->from('ulice')->where('obec = %u',$obec)->orderBy('name')->execute();
               echo '[';
               foreach($res as $i=>$o){
                    if($i!=0) echo ',';
                    if($format=='array')
                         echo '"'.$o->name.'"';
                    else
                         echo '{"value":"'.$o->id.'","name":"'.$o->name.'"}';
               }
               echo ']';
               $db_uzemi->disconnect();
          }
     }

     public function loadOkrsekByUlice($ulice){
          if(preg_match('/^[0-9]+$/',$ulice)){

               require(dirname(__FILE__).'/db.php');
               $db_uliceokrsek = getdb('uliceokrsek');
    
               $okrsky_id = $db_uliceokrsek->select('okrsek_id')->from('ulice_okrsek')->where('ulice_id = %u',$ulice)
                    ->execute();

               $db_uzemi = getdb('uzemi');
               $res = $db_uzemi->select('id,num')
                    ->from('okrsek')->where('id IN %in',$okrsky_id)
                    ->execute();     

               // ovk
               $db_ovk = getdb('ovk');
               if($db_ovk->getConfig('driver')=='sqlite3')
                    $test = $db_ovk->query('SELECT name FROM sqlite_master WHERE type="table" AND name="ovk"')->fetchSingle();
               else {
                    $test = $db_ovk->query('SELECT * FROM information_schema.tables WHERE table_schema = "'.$db_ovk->getConfig('database').'" AND table_name = "ovk" LIMIT 1')->fetchSingle();
               }
               
               $disabled = array();
               if($test!=false){
                    $res2 = $db_ovk->select('okrsek')
                         ->from('ovk')->where('cancel = %b',false)->execute();     
                    foreach($res2 as $i=>$o){
                         $disabled[] = $o->okrsek;
                    }
               }
               $db_ovk->disconnect();
               // ovk end


               echo '[';
               foreach($res as $i=>$o){
                    if($i!=0) echo ',';
                    echo '{';
                         echo '"value":"'.$o->id.'","name":"'.$o->num.'"';
                         if(in_array($o->id,$disabled)) echo ',"disabled":"disabled"';
                    echo '}';
               }
               echo ']';
          }
     }

     public function loadOkrsek($obec){
          if(preg_match('/^[0-9]+$/',$obec)){
               require(dirname(__FILE__).'/db.php');

               // ovk
               $db_ovk = getdb('ovk');
               if($db_ovk->getConfig('driver')=='sqlite3')
                    $test = $db_ovk->query('SELECT name FROM sqlite_master WHERE type="table" AND name="ovk"')->fetchSingle();
               else {
                    $test = $db_ovk->query('SELECT * FROM information_schema.tables WHERE table_schema = "'.$db_ovk->getConfig('database').'" AND table_name = "ovk" LIMIT 1')->fetchSingle();
               }
               $disabled = array();
               if($test!=false){
                    $res2 = $db_ovk->select('okrsek')
                         ->from('ovk')->where('cancel = %b',false)->execute();     
                    foreach($res2 as $i=>$o){
                         $disabled[] = $o->okrsek;
                    }
               }
               $db_ovk->disconnect();
               // ovk end

               $db_uzemi = getdb('uzemi');
               $res = $db_uzemi->select('id,num')->from('okrsek')
                    ->where('obec = %u',$obec)->orderBy('num')->execute();

               echo '[';
               foreach($res as $i=>$o){
                    if($i!=0) echo ',';
                    echo '{';
                         echo '"value":"'.$o->id.'","name":"'.$o->num.'"';
                         if(in_array($o->id,$disabled)) echo ',"disabled":"disabled"';
                    echo '}';
               }
               echo ']';
               $db_uzemi->disconnect();
          }
     }

}

