<?php
require_once('StringWidget.php');
class EmailWidget extends StringWidget {
     public function renderWidget(){
          $this->setOption('type','email');
          return parent::renderWidget();
     }     
}
