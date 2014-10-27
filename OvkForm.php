<?php
class OvkPiratiForm extends PiratiForm {

     function init(){

          require_once(DOKU_PLUGIN . '/piratiform/db.php');
          $db = getdb('ovk')->getConfig();

          // form
          $this->setOption('edit',true);
          $this->setOption('cancel',true);
          $this->setOption('canceled','Vaše registrace byla zrušena. Informace byla také zaslána na váš e-mail.');
          $this->setOption('canceledhideform',true);
          $this->setOption('cancel_button','Zrušit registraci');
          $this->setOption('cancel_confirm','Opravdu si přejete zrušit registraci?');
          $this->setOption('cancel_mail',"Vaše registrace do volební okrskové komise na stránkách %url% byla zrušena.\n\nArrr! Piráti.\n");
          $this->setOption('cancel_subject','Zrušení registrace do OVK - pirati.cz');
          $this->setOption('cancel_db',$db);
          $this->setOption('cancel_table','ovk');
          $this->setOption('endtime',mktime(23,59,59,9,5,2014));
//        $this->setOption('endtime',mktime(16,38,59,3,28,2014));
          $this->setOption('endtime_msg','Registrace do Okrskových volebních komisí byly ukončeny.');
          $this->setOption('resend',true);
          $this->setOption('admin',true);
          $this->setOption('admin_key','pyrat22');
          $this->setOption('saved','Registrace proběhla v pořádku. Na váš E-mail by měly přijít vyplněné informace.');
          $this->setOption('savedhideform',true);
          $this->setOption('progress',true);
          $this->setOption('progress_limit',2000);

          $this->addAction(array(
               'type' => 'db',
               'db' => $db,
               'table' => 'ovk',
               'unique' => array('rc'=>'Stejné rodné číslo je již registrováno','okrsek'=>'Tento okrsek je již zabraný. Vyberte si jiný.')
          ));
          $this->addAction(array(
               'type' => 'mail',
               'from' => 'okrsky@pirati.cz',
               'tofield' => 'email',
               'subject' => 'Registrace do OVK - pirati.cz',
               'body' => "Dobrý den,\nzasíláme vám pro kontrolu údaje, které jste vyplnili na stránkách: %url%\n\nRekapitulace zadaných údajů:\n%data%\n\nRegistrace budou ukončeny 5.9. 2014 23:59:59.\nKomunální volby se budou konat 10. a 11. října 2014.\nV komisi nemůžete pracovat, pokud kandidujete v daném místě.\nV komisi lze být pouze za jeden politický subjekt.\nPoté co od nás obdrží obec informace o vás, měla by vám zaslat do datové schránky nebo na korespondenční adresu informace o první schůzi komise. Doporučujeme je kontaktovat a zjistit si termín, vše je již v režii starosty.\n\nDo konce registrací můžete své údaje aktualizovat nebo svou registraci zrušit. Případně si nechat zaslat údaje znova. Vše lze provést na této adrese\n %userurl%\n\nArrr! Piráti.\n",
               'dataout' => array('filterstreet')
          ));

          //
          $this->addHtml('required','<br>Pole označená <span class="label label-important">!</span> jsou povinná. Pro vyplnění je také nutné mít zapnutý javascript.');
          // fieldsets
          $this->addFieldset('person','Osoba',array(
               // wigets
               //'obcan' => new SelectWidget(array('label'=>'Státní občanství','help'=>'Pokud nemáte státní občanství ČR a přesto chcete být delegováni, můžete napsat na e-mail uvedený vpravo.','choices'=>array('1'=>'Česká Republika'),'default'=>1,'multiple'=>true,'expanded'=>true),array('readonly'=>'readonly')),
               'name' => new StringWidget(array('label'=>'Jméno','help'=>'Vyplňte vaše jméno.')),
               'surname' => new StringWidget(array('label'=>'Příjmení','help'=>'Vyplňte vaše příjmení.')),
               'degbefore' => new StringWidget(array('label'=>'Titul před jménem','help'=>'Vyplňte váš titul před jménem.')),
               'degafter' => new StringWidget(array('label'=>'Titul za jménem','help'=>'Vyplňte váš titul za jménem..')),

               'rc' => new RcWidget(array('label'=>'Rodné číslo','help'=>'Vyplňte vaše rodné číslo ve tvaru 000000/0000 nebo 000000/000.'),array(
                    'placeholder'=>'000000/000(0)','pattern'=>'[0-9]{6}/[0-9]{3,4}'
               ))
          ));
          $this->addFieldset('contact','Kontakt',array(
               'email' => new EmailWidget(array('label'=>'E-mail','help'=>'Vyplňte vaši e-mailovou adresu. Na tuto adresu vám budou také zaslány vyplněné informace a také vás na ní budeme informovat o dalším postupu.')),
               'phone' => new StringWidget(array('label'=>'Telefon','help'=>'Telefon (pouze 9 číslic bez mezer, případně lze použít i mezinárodní předvolbu ve tvaru +000). Telefon slouží pouze pro případ, že by nastaly nějaké výjimečné problémy (jako chybně uvedený e-mail, adresa apod.)'),array('pattern'=>'(\+[0-9]{3})?[0-9]{9}')),
               'idds' => new StringWidget(array('label'=>'ID datové schránky','help'=>'ID datové schránky, slouží k zaslání informací od úřadu (informace o prvním zasedání apod.). Není nutné vyplňovat.'),array('pattern'=>'[a-z0-9]{7}'))
          ));
          $this->addFieldset('address','Adresa trvalého pobytu',array(
               'region' => new KrajWidget(array('label'=>'Kraj','help'=>'Vyberte kraj.',
                    'events' => array(
                         // okres
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_district'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_district'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_district'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'okres','id'=>'piratiform_district'),
                         // obec
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_town'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_town'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_town'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_town'),
                         // cast
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_cast'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_cast'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_cast'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_cast'),
                         // ulice
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_street'),
                         // okrsek
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_street')
                    )
               )),
               'district' => new OkresWidget(array('label'=>'Okres','help'=>'Vyberte okres.','choices'=>array(),
                    'events' => array(
                         // okres
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_district'), 
                         // obec
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_town'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_town'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_town'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'obec','id'=>'piratiform_town'),
                         // cast
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_cast'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_cast'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_cast'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_cast'), 
                         // ulice
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_street'),
                         // okrsek
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_street')
                    )
               ),array('disabled'=>'disabled')),
               'town' => new ObecWidget(array('label'=>'Obec','help'=>'Vyberte obec.','choices'=>array(),
                    'events' => array(
                         // obec
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_town'),
//                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_okrsek'),
                         array('trigger'=>'init','type'=>'ajaxload','data'=>'okrsek','id'=>'piratiform_okrsek'),
                         // cast
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_cast'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'cast','id'=>'piratiform_cast'),
                         // ulice
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_street'),
                         array('trigger'=>'change','type'=>'typeaheadload','data'=>'ulice_array','id'=>'piratiform_street'),
                         // ulice u okrsku
                         //array('trigger'=>'change','type'=>'ajaxload','data'=>'ulice','id'=>'piratiform_filterstreet'),
                         // okrsek
                         //array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_okrsek'),
                         //array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_okrsek'),
                         //array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_okrsek'),
                         //array('trigger'=>'change','type'=>'ajaxload','data'=>'okrsek','id'=>'piratiform_okrsek')
                    )
               ),array('disabled'=>'disabled')),
               'cast' => new CastWidget(array('label'=>'Část obce','help'=>'Vyberte část obce.','choices'=>array(),
                    'events' => array(
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_cast'),
                    )
               ),array('disabled'=>'disabled')),
                    'street' => new StringWidget(array('label'=>'Ulice','help'=>'Napište ulici. Při psaní názvu vám bude formulář napovídat. Pokud obec nemá žádnou konkrétní ulici, můžete vyplnit opět část obce, či název obce.',
                    'events'=> array(
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_street'),
                    )
               ),array('disabled'=>'disabled')),
               'cp' => new StringWidget(array('label'=>'Číslo popisné/orientační','help'=>'Číslo popisné, případně i orientační (ve tvaru 0000/00).'),array('pattern'=>'[0-9a-z]+(/[0-9a-z]+)*','placeholder'=>'0000(/00)')),
               'psc' => new StringWidget(array('label'=>'PSČ','help'=>'Vyplňte vaše poštovní směrovací číslo (číslice bez mezer).'),array('pattern'=>'[0-9]{5}','placeholder'=>'00000'))
          ));
          $this->addFieldset('address2','Korespondenční adresa',array(
               'sameadr' => new SelectWidget(array(
                    'multiple'=>true,'expanded'=>true,
                    'choices'=>array('1'=>'Stejná jako adresa trvalého pobytu'),
                    'default'=>1,
                    'events' => array(
                         array('trigger'=>'init','type'=>'hide','class'=>'sameadr','value'=>1),
                         array('trigger'=>'change','type'=>'hidetoggle','class'=>'sameadr'),
                         array('trigger'=>'change','type'=>'required','class'=>'sameadr')
                    )
               )),
               'region2' => new KrajWidget(array('label'=>'Kraj','rowclass'=>'sameadr','help'=>'Vyberte kraj.',
                    'events'=>array(
                         // okres 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_district2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_district2'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_district2'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'okres','id'=>'piratiform_district2'),
                         // obec 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_town2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_town2'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_town2'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_town2'),
                         // cast 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_cast2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_cast2'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_cast2'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_cast2'),
                         // ulice 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_street2')
                    )
               )),
               'district2' => new OkresWidget(array('label'=>'Okres','rowclass'=>'sameadr','help'=>'Vyberte okres.','choices'=>array(),
                    'events'=>array(
                         // okres 2
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_district2'),
                         // obec 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_town2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_town2'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_town2'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'obec','id'=>'piratiform_town2'),
                         // cast 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_cast2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_cast2'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_cast2'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_cast2'),
                         // ulice 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_street2')
                    )
               ),array('disabled'=>'disabled')),
               'town2' => new ObecWidget(array('label'=>'Obec','rowclass'=>'sameadr','help'=>'Vyberte obec.','choices'=>array(),
                    'events'=>array(
                         // obec 2
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_town2'),
                         // cast 2
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_cast2'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'cast','id'=>'piratiform_cast2'),
                         // ulice 2
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_street2'),
                         array('trigger'=>'change','type'=>'typeaheadload','data'=>'ulice_array','id'=>'piratiform_street2')
                    )
               ),array('disabled'=>'disabled')),
               'cast2' => new CastWidget(array('label'=>'Část obce','rowclass'=>'sameadr','help'=>'Vyberte část obce.','choices'=>array(),
                    'events' => array(
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_cast2')
                    )
               ),array('disabled'=>'disabled')),
               'street2' => new StringWidget(array('label'=>'Ulice','rowclass'=>'sameadr','help'=>'Napište ulici. Při psaní názvu vám bude formulář napovídat. Pokud obec nemá žádnou konkrétní ulici, můžete vyplnit opět část obce, či název obce.',
                    'events' => array(
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_street2')
                    )
               ),array('disabled'=>'disabled')),
               'cp2' => new StringWidget(array('label'=>'Číslo popisné/orientační','rowclass'=>'sameadr','help'=>'Číslo popisné, případně i orientační (ve tvaru 0000/00).'),array('pattern'=>'[0-9a-z]+(/[0-9a-z]+)*','placeholder'=>'0000(/00)')),
               'psc2' => new StringWidget(array('label'=>'PSČ','rowclass'=>'sameadr','help'=>'Vyplňte vaše poštovní směrovací číslo (číslice bez mezer).'),array('pattern'=>'[0-9]{5}','placeholder'=>'00000'))
          ));
          $this->addFieldset('ovk','Místo delegace',array(
               /*'ovk_sameadr' => new SelectWidget(array(
                    'multiple'=>true,'expanded'=>true,
                    'choices'=>array('1'=>'Stejné jako trvalého pobytu'),
                    'default'=>1,
                    'events' => array(
                         array('trigger'=>'init','type'=>'hide','class'=>'ovk_sameadr','value'=>1),
                         array('trigger'=>'change','type'=>'hidetoggle','class'=>'ovk_sameadr'),
                         array('trigger'=>'change','type'=>'required','class'=>'ovk_sameadr')
                    )
               )),*/
               'ovk_region' => new KrajOvkWidget(array('label'=>'Kraj','rowclass'=>'ovk_sameadr','help'=>'Vyberte kraj.',
                    'events'=>array(
                         // okres ovk
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_ovk_district'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_ovk_district'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_ovk_district'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'okres_ovk','id'=>'piratiform_ovk_district'),
                         // obec ovk
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_ovk_town'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_ovk_town'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_ovk_town'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_ovk_town'),
                         // cast ovk
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_ovk_cast'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_ovk_cast'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_ovk_cast'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_ovk_cast'),
                         // ulice u okrsku
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_filterstreet'),
                         // okrsek
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_okrsek')
                    )
               )),
               'ovk_district' => new OkresOvkWidget(array('label'=>'Okres','rowclass'=>'ovk_sameadr','help'=>'Vyberte okres.','choices'=>array(),
                    'events'=>array(
                         // okres ovk
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_ovk_district'),
                         // obec ovk
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_ovk_town'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_ovk_town'),
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_ovk_town'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'obec_ovk','id'=>'piratiform_ovk_town'),
                         // cast ovk
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_ovk_cast'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_ovk_cast'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_ovk_cast'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_ovk_cast'),
                         // ulice u okrsku
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_filterstreet'),
                         // okrsek
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'disableonfull','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_okrsek')
                    )
               ),array('disabled'=>'disabled')),
               'ovk_town' => new ObecOvkWidget(array('label'=>'Obec','rowclass'=>'ovk_sameadr','help'=>'Vyberte obec.','choices'=>array(),
                    'events'=>array(
                         // obec ovk
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_ovk_town'),
                         // cast 2
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_ovk_cast'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'cast','id'=>'piratiform_ovk_cast'),
                         // ulice u okrsku
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'emptyonfull','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'ulice','id'=>'piratiform_filterstreet'),
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_filterstreet'),
                         // okrsek
                         array('trigger'=>'change','type'=>'enableonfull','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'emptyonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'disableonempty','id'=>'piratiform_okrsek'),
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'okrsek','id'=>'piratiform_okrsek')
                    )
               ),array('disabled'=>'disabled')),
               'ovk_cast' => new CastWidget(array('label'=>'Část obce','rowclass'=>'ovk_sameadr','help'=>'Vyberte část obce.','choices'=>array(),
                    'events' => array(
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_ovk_cast'),
                    )
               ),array('disabled'=>'disabled')),
               'filterstreet' => new UliceWidget(array('label'=>'Pouze okrsky z této ulice','help'=>'Můžete zde pro přesnější výběr okrsku zadat ulici a formulář vám poté zobrazí pouze okrsky, které do této ulice zasahují. Některé okrsky však nemusí do žádné ulice patřit.','choices'=>array(),
                    'events' => array(
                         array('trigger'=>'change','type'=>'ajaxload','data'=>'okrsekulice','id'=>'piratiform_okrsek')
                    )
               ),array('disabled'=>'disabled')),
               'okrsek' => new OkrsekWidget(array('label'=>'Okrsek','help'=>'Vyberte volební okrsek. V případě, že okrsek nelze vybrat, tak je již obsazený. Okrsek si můžete zkontrolovat i pomocí odkazu na mapu, který se po vybrání okrsku zobrazí.','choices'=>array(),
                    'events'=> array(
                         array('trigger'=>'init','type'=>'enableonfull','id'=>'piratiform_okrsek'),
                    )
               ),array('disabled'=>'disabled'))
          ));
          $this->addWidget('futureovk',new SelectWidget(array(
               'multiple' => true,
               'expanded' => true,
               'choices' => array('1'=>'Chci být v budoucnu informován za účelem registrace do Okrskových volebních komisí'),
               'help' => 'V případě zaškrtnutí se uloží Váš e-mail a v budoucnu budete při dalších volbách informováni o spuštění formuláře.'
          )));
          $this->addWidget('help',new SelectWidget(array(
               'multiple' => true,
               'expanded' => true,
               'choices' => array('1'=>'Chci pomoci Pirátům s kampaní'),
               'help' => 'Pokud zaškrtnete toto políčko, tak budou Váš e-mail, telefon a místo delegace předány krajskému předsedovi Pirátů, který Vás bude kontaktovat s informacemi, jak můžete pomoci Pirátům v kampani.'
          )));
          $this->addWidget('confirm',new SelectWidget(array(
               'multiple' => true,
               'expanded' => true,
               'choices' => array('1'=>'Souhlasím se zpracováním osobních údajů')
          )));
          $this->addAdminFieldset('admin_note','Administrace',array(
               'note' => new TextWidget(array('label'=>'Poznámka'))
          ));
          
          // validators
          //$this->addValidator('obcan', new SelectValidator(array('choices'=>array('1'=>'Česká republika'))));
          $this->addValidator('name', new StringValidator());
          $this->addValidator('surname', new StringValidator());
          $this->addValidator('degbefore', new StringValidator(array('required'=>false)));
          $this->addValidator('degafter', new StringValidator(array('required'=>false)));
          $this->addValidator('rc', new RcValidator(array('minage'=>18,'agedate'=>'2014-10-10')));
          //
          $this->addValidator('email', new EmailValidator());
          $this->addValidator('phone', new RegexpValidator(array('regexp'=>'^[0-9]{9}$')));
          $this->addValidator('idds', new IddsValidator(array('required'=>false)));
          //
          $this->addValidator('region', new KrajValidator());
          $this->addValidator('district', new OkresValidator());
          $this->addValidator('town', new ObecValidator());
          $this->addValidator('cast', new CastValidator());
          $this->addValidator('street', new UliceValidator());
          $this->addValidator('cp', new RegexpValidator(array('regexp'=>'^[0-9a-z]+(\/[0-9a-z]+)*$')));
          $this->addValidator('psc', new RegexpValidator(array('regexp'=>'^[0-9]{5}$')));
          //
          $this->addValidator('sameadr', new SelectValidator(array('required'=>false)));
          $this->addValidator('region2', new KrajValidator(array('required'=>false)));
          $this->addValidator('district2', new OkresValidator(array('required'=>false)));
          $this->addValidator('town2', new ObecValidator(array('required'=>false)));
          $this->addValidator('cast2', new CastValidator(array('required'=>false)));
          $this->addValidator('street2', new UliceValidator(array('required'=>false)));
          $this->addValidator('cp2', new RegexpValidator(array('regexp'=>'^[0-9a-z]+(\/[0-9a-z]+)*$','required'=>false)));
          $this->addValidator('psc2', new RegexpValidator(array('regexp'=>'^[0-9]{5}$','required'=>false)));
          //
          //$this->addValidator('ovk_sameadr', new SelectValidator(array('required'=>false)));
          $this->addValidator('ovk_region', new KrajOvkValidator(array('required'=>true)));
          $this->addValidator('ovk_district', new OkresOvkValidator(array('required'=>true)));
          $this->addValidator('ovk_town', new ObecOvkValidator(array('required'=>true)));
          $this->addValidator('ovk_cast', new CastValidator(array('required'=>true)));
          //
          $this->addValidator('filterstreet', new UliceValidator(array('required'=>false)));
          $this->addValidator('okrsek', new OkrsekValidator());
          //
          $this->addValidator('help', new SelectValidator(array('required'=>false)));
          $this->addValidator('futureovk', new SelectValidator(array('required'=>false)));
          $this->addValidator('confirm', new SelectValidator());
          //
          $this->addValidator('note', new StringValidator(array('required'=>false)));
     }
}
