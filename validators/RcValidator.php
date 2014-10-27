<?php
require_once('Validator.php');
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
