<?php
require_once('StringWidget.php');
class NumberWidget extends StringWidget {
     public function renderWidget(){
          $this->setOption('type','number');
          return parent::renderWidget();
     }
}
