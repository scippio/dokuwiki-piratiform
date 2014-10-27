<?php
/**
 * Bureaucracy Plugin: Allows flexible creation of forms
 *
 * This plugin allows definition of forms in wiki pages. The forms can be
 * submitted via email or used to create new pages from templates.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Adrian Lang <dokuwiki@cosmocode.de>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_piratiform extends DokuWiki_Syntax_Plugin {
    // allowed types and the number of arguments
    private $form_id = 0;

    /**
     * What kind of syntax are we?
     */
    public function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    public function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    public function getSort(){
        return 155;
    }


    /**
     * Connect pattern to lexer
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<piratiform>.*?</piratiform>',$mode,'plugin_piratiform');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler){

         // TODO: json syntax generate form class

          //_dump(DOKU_LEXER_EXIT);
         //var_dump($state,$match); die();
         
         switch($state){
               case DOKU_LEXER_ENTER:
               case DOKU_LEXER_MATCHED:
               case DOKU_LEXER_UNMATCHED:
               case DOKU_LEXER_EXIT:
               case DOKU_LEXER_SPECIAL:
                    preg_match('/<piratiform>(.*)<\/piratiform>/',$match,$m);
                    return array('data'=>$m[1]);
                    break;
         }
         return array();
         // return array('data'=>$match);
          //var_dump($match);
         //die();
         //return array('fields'=>$cmds,'actions'=>$actions,'thanks'=>$thanks,'labels'=>$labels);
    }

    /**
     * Create output
     */
     public function render($format, Doku_Renderer &$R, $data) {
          global $ID;
          if ($format != 'xhtml') return false;
          $R->info['cache'] = false; // don't cache

/*        if ($_SERVER['HTTP_X_PROTOCOL'] !== 'https') { 
               header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']); exit(); 
          }
*/
          // form --
          require(DOKU_PLUGIN.'piratiform/Form.php');

          // TODO: require class from data/ place
          
          //require(DOKU_PLUGIN.'piratiform/OvkForm.php');
          //$form = new OvkPiratiForm();

          //var_dump($data);
          //die(); 
          
          if($data['data'] == "cf"){
               require(DOKU_PLUGIN.'piratiform/CfForm.php');
               $form = new CfPiratiForm();
          }

          if(isset($_GET['piratiform']) and $form->getOption('edit',false)){
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
                              //send_redirect(wl($ID));
                         }
                    }
               }
               $R->doc .= $form->render();
          } elseif(isset($_GET['sendmails'])){
               global $ID;
               die('off');
               echo 'mails...';
               dibi::connect(array(
                    'driver' => 'sqlite3',
                    'database' => DOKU_PLUGIN.'/piratiform/mails.db'
               ));
               $m = dibi::select('*')->from('mails')->where('hash IS NULL')->execute();
               foreach($m as $n){
                    $hash = sha1(rand(0,1000).date('YmdHis').microtime());
                    $body = "Dobrý den,\nv minulosti jste projevili souhlas s informováním ohledně registrací do Okrskové volební komise za Českou pirátskou stranu.\nProto Vám zasíláme tento e-mail abychom upozornili na zahájení registrace do OVK v rámci voleb do Evropského parlamentu, které proběhnou 23. a 24. května. Naše kandidáty si můžete prohlédnout zde spolu s dalšími informacemi:\nhttp://www.pirati.cz/volby2014/kandidatka\n\nRegistrovat se do OVK můžete zde: ".wl($ID)."\n\nPokud si NEpřejete v budoucnu dostávat další upozornění ohledně Okrskových volebních komisí, můžete se odhlásit na tomto odkaze:\n ".wl($ID)."?removemail=".$n->mail."&hash=".$hash."\n";
                    if(mail_send($n->mail,'Registrace do Okrskovych volebnich komisi - pirati.cz',$body,'okrsky@pirati.cz')){
                         dibi::update('mails',array(
                              'hash%s' => $hash
                         ))->where('mail = %s',$n->mail)->execute();
                    }
               }

          } elseif(isset($_GET['removemail'])){
               global $ID;

               if(filter_var($_GET['removemail'],FILTER_VALIDATE_EMAIL) and preg_match('/^[a-z0-9]+$/',$_GET['hash'])){
                    if(isset($_GET['disable'])){
                         dibi::connect(array(
                              'driver' => 'sqlite3',
                              'database' => DOKU_PLUGIN.'/piratiform/test_mails.db'
                         ));
                         $res = dibi::update('mails',array('remove%i'=>1))->where('mail = %s',$_GET['removemail'])->and('hash = %s',$_GET['hash'])->execute();
                         if($res){
                              $R->doc .= '<div class="alert alert-success"><strong>E-mail byl úspěšně odebrán z databáze</strong></div>';
                         } else {
                              $R->doc .= '<div class="alert alert-danger"><strong>E-mail se nepodařilo odebrat</strong><br>Pro odebrání e-mailu z databáze napište na <a href="mailto:okrsky@pirati.cz">okrsky@pirati.cz</a></div>';
                         }
                    } else {
                         $R->doc .= '<div style="text-align:center">';
                              $R->doc .= '<a href="'.wl($ID).'?removemail='.$_GET['removemail'].'&hash='.$_GET['hash'].'&disable" class="btn btn-danger">Odstranit z databáze e-mail pro informování o OVK</a>';
                         $R->doc .= '</div>';
                    }
               } else {
                    $R->doc .= '<div class="alert alert-danger"><strong>Chybný e-mail</strong><br>Pro odebrání e-mailu z databáze napište na <a href="mailto:okrsky@pirati.cz">okrsky@pirati.cz</a></div>';
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
                    if(!isset($_GET['key']) or $_GET['key']!='piratidoeu' ){
                         //$R->doc .= '<div class="alert alert-info"><strong>Chybný klíč!</strong> - pro veřejnost budou registrace spuštěny v pondělí 31. března.<br>Pokud jste se již registrovali, tak potřebné informace budou v e-mailu, který by vám měl přijít.</div>';
                         //return true;
                    } else {
                         $R->doc .= '<div class="alert alert-danger"><strong>Chybná URL!</strong>- Tato stránka již není aktuální</div>';
                         return true;
                    }
               }
               $R->doc .= $form->render();
          }

          return true;
     }
}

