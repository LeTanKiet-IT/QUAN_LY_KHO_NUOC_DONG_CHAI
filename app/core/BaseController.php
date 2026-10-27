<?php
class BaseController {
 protected function render(string $view,array $data=[]):void { extract($data); require __DIR__.'/../views/layout/header.php'; require __DIR__.'/../views/'.$view.'.php'; require __DIR__.'/../views/layout/footer.php'; }
 protected function go(string $path):never { header('Location: '.BASE_URL.$path); exit; }
 protected function msg(string $type,string $text):void { $_SESSION['flash']=['type'=>$type,'text'=>$text]; }
}
