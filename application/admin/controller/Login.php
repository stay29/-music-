<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\facade\Session;
class Login  extends Controller
{
   public function index(){
        return view();
   }
   public function log(){
    $data = input('post.');
    $data['status'] = 1;
    $res = Db::table('erp2_admins')->where($data)->find();
    if($res){
      Session::set('aid',$res['admin_id']);
      Session::set('username',$res['account']);
      Session::set('rid',$res['rid']);
      echo "1";
    }else{
      echo "2";
    }
   } 
   public function logout(){
    session(null);
   $this->redirect(url('admin/login/index'));
   }
}