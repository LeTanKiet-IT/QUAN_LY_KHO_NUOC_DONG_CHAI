<?php
class Auth {
 static function user():?array{return $_SESSION['user']??null;}
 static function loginRequired():void{if(!self::user()){header('Location: '.BASE_URL.'/login');exit;}}
 static function allow(string $permission):void{self::loginRequired();$u=self::user();if(($u['TenVaiTro']??'')!=='Admin'&&!in_array($permission,$u['permissions']??[],true)){http_response_code(403);exit('403 - Tài khoản không có quyền thực hiện chức năng này.');}}
 static function log(?string $id,string $action,string $obj='',string $objId='',string $note=''):void{try{$s=Database::conn()->prepare('INSERT INTO lich_su_thao_tac(MaTaiKhoan,HanhDong,DoiTuong,MaDoiTuong,NoiDung) VALUES(?,?,?,?,?)');$s->execute([$id,$action,$obj?:null,$objId?:null,$note?:null]);}catch(Throwable $e){error_log($e->getMessage());}}
}
