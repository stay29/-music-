<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\response\Json;
use think\facade\Session;
use think\App;
use think\facade\Request;
class Comm  extends Controller
{
 public function __construct(){
 parent::__construct();
       $rid = Session::get('rid');
       $aid = Session::get('aid');
       if(!$aid){
           $this->redirect(url('admin/login/index'));
       }
       if($rid==null){
           $this->redirect(url('admin/login/index'));
       } 
}
   
}