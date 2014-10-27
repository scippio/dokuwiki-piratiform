<?php
date_default_timezone_set('Europe/Prague');

function getSecurityToken(){
     return 'p';
}
function checkSecurityToken(){
//     if(is_null($token)) $token = $_REQUEST['sectok'];
     //if(getSecurityToken() != $token){
       //   return false;
//     }
     return true;
}
function send_redirect($url){
     header('Location: '.$url);
     exit();
}
include(dirname(__FILE__).'/mail.php');
function wl($id){
     return 'https://www.pirati.cz/'.$id;
}
//$ID = 'volby/ovk';
$ID = 'ovk/index.php';
define('DOKU_PLUGIN',dirname(__FILE__).'/../');
$out = '';

//          if ($format != 'xhtml') return false;
//          $R->info['cache'] = false; // don't cache

/*          if ($_SERVER['HTTP_X_PROTOCOL'] !== 'https') { 
               header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']); exit(); 
          }
 */

          // form --
          require(DOKU_PLUGIN.'piratiform/Form.php');

          // TODO: require class from data/ place
          require(DOKU_PLUGIN.'piratiform/OvkForm.php');
          $form = new OvkPiratiForm();
          
          if(isset($_GET['piratiform']) and $form->getOption('edit',false)){

               if(!isset($_GET['tseed'])){
                    $uri = $_SERVER['REQUEST_URI'];
                    if(strpos($uri,'?')===false) $s = '?tseed='.time();
                    else $s = '&tseed='.time();
                    header('Location: '.$uri.$s); exit();
               }

               $form->edit($_GET['piratiform']);

               if($form->getOption('resend')){
                    if(isset($_GET['resend'])){
                         $form->resend();
                    }
               }
               if($form->getOption('cancel')){
                    if(isset($_GET['cancel'])){
                         $form->cancel();
                         send_redirect(wl($ID).'?canceled');
                    }
               }

               // post
               if(isset($_POST[$form->getOption('name','piratiform')]) && checkSecurityToken()){
                    $form->bind($_POST);
                    if($form->validate()){
                         if($form->action()){
                              //send_redirect(wl($ID).'?'.$form->getOption('name','piratiform').'='.$form->getOption('userid').'-'.$form->getOption('hash').'&tseed='.time());
                         }
                    }
               }
               $out .= $form->render();
          } elseif(isset($_GET['sendmails'])){
               // TODO: use send_mails.php !!
               /*dibi::connect(array(
                    'driver' => 'mysqli',
                    'database' => '',
                    'host' => '',
                    'username' => '',
                    'password' => '',
                    'charset' => 'utf8'
               ));
               $m = dibi::select('id,email,hash')->from('ovk_mails')
                    ->where('status = 0')->execute(); //->and('email = %s','vaclav.malek@pirati.cz')->execute();
               $i=0;
               echo count($m)."--";// die();
               foreach($m as $n){
                    $i++;
                    $body = "Dobrý den,\nv minulosti jste projevili souhlas s informováním ohledně registrací do Okrskové volební komise za Českou pirátskou stranu.\nProto Vám zasíláme tento e-mail abychom upozornili na zahájení registrace do OVK v rámci voleb do zastupitelstev obcí a do Senátu, které proběhnou 10. a 11. října.\nPodrobné informace o kandidatuře Pirátů v komunálních a senátních volbách najdete na http://www.volimpiraty.cz .\n\nPřehled obcí, v nichž můžeme do OVK delegovat, a návod k registraci naleznete zde: http://www.pirati.cz/volby/2014/komunal/ovk-obce\n\n\n\nPokud si NEpřejete v budoucnu dostávat další upozornění ohledně Okrskových volebních komisí, můžete se odhlásit na tomto odkaze:\n".wl($ID)."?removemail=".$n->email."&hash=".$n->hash."\n";
                    $o = mail_send($n->email,'Registrace do Okrskovych volebnich komisi - pirati.cz',preg_replace('/(?=\s)(.{1,70})(?:\s|$)/uS','$1'."\n",$body),'okrsky@pirati.cz');
                    if(is_null($o)){
                         dibi::update('ovk_mails',array(
                              'status' => 1
                         ))->where('id = %i',$n->id)->execute();
                    }
               }
               die('done: '.$i);*/
          } elseif(isset($_GET['removemail'])){
               if(filter_var($_GET['removemail'],FILTER_VALIDATE_EMAIL) and preg_match('/^[a-z0-9]+$/',$_GET['hash'])){
                    if(isset($_GET['disable'])){
                         dibi::connect(array(
                              'driver' => 'mysqli',
                              'database' => '',
                              'host' => '',
                              'username' => '',
                              'password' => '',
                              'charset' => 'utf8'
                         ));
                         $res = dibi::update('ovk_mails',array('status%i'=>2))->where('email = %s',$_GET['removemail'])->and('hash = %s',$_GET['hash'])->execute();
                         if($res){
                              $out .= '<div class="alert alert-success"><strong>E-mail byl úspěšně odebrán z databáze</strong></div>';
                         } else {
                              $out .= '<div class="alert alert-danger"><strong>E-mail se nepodařilo odebrat</strong><br>Pro odebrání e-mailu z databáze napište na <a href="mailto:okrsky@pirati.cz">okrsky@pirati.cz</a></div>';
                         }
                    } else {
                         $out .= '<div style="text-align:center">';
                              $out .= '<a href="'.wl($ID).'?removemail='.$_GET['removemail'].'&hash='.$_GET['hash'].'&disable" class="btn btn-danger">Odstranit z databáze e-mail pro informování o OVK</a>';
                         $out .= '</div>';
                    }
               } else {
                    $out .= '<div class="alert alert-danger"><strong>Chybný e-mail</strong><br>Pro odebrání e-mailu z databáze napište na <a href="mailto:okrsky@pirati.cz">okrsky@pirati.cz</a></div>';
               }
               return true;
          } else {
               // post
               if(isset($_POST[$form->getOption('name','piratiform')]) && checkSecurityToken()){

                    $form->bind($_POST);
                    if($form->validate()){
                         if($form->action()){
                              send_redirect(wl($ID).'?saved');
                         }
                    }
               } else {
                    // keymaster
                    //if(!isset($_GET['saved']) and !isset($_GET['canceled']))
                    if(!isset($_GET['key']) or $_GET['key']!='piratidoeudddfggh' ){
                         //$R->doc .= '<div class="alert alert-info"><strong>Chybný klíč!</strong> - pro veřejnost budou registrace spuštěny v pondělí 31. března.<br>Pokud jste se již registrovali, tak potřebné informace budou v e-mailu, který by vám měl přijít.</div>';
                         //return true;
                    } else {
                         $out .= '<div class="alert alert-danger"><strong>Chybná URL!</strong>- Tato stránka již není aktuální</div>';
                         return true;
                    }
               }
               $out .= $form->render();
          }
