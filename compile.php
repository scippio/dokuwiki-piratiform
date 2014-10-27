<?php

echo '<?php';

$validators = array(
     'Validator','StringValidator','NumberValidator','RegexpValidator',
     'SelectValidator','EmailValidator','IddsValidator','RcValidator',
     'KrajValidator','KrajOvkValidator','OkresValidator','OkresOvkValidator','ObecValidator','ObecOvkValidator','CastValidator',
     'UliceValidator','OkrsekValidator'
);
$widgets = array(
     'Widget','StringWidget','SelectWidget','EmailWidget',
     'NumberWidget','WhisperWidget','RcWidget','KrajWidget','KrajOvkWidget',
     'OkresWidget','OkresOvkWidget','ObecWidget','ObecOvkWidget','CastWidget','UliceWidget','OkrsekWidget',
     'TextWidget'
);

foreach($validators as $val){
     $c = str_replace('<?php','',file_get_contents('validators/'.$val.'.php'));
     $c = preg_replace("/require_once\([A-Za-z'.]+\);/",'',$c);
     $c = str_replace('/../db.php','/db.php',$c);
     echo $c;
}

foreach($widgets as $val){
     $c = str_replace('<?php','',file_get_contents('widgets/'.$val.'.php'));
     $c = preg_replace("/require_once\([A-Za-z'.]+\);/",'',$c);
     $c = str_replace('/../db.php','/db.php',$c);
     echo $c;
}

