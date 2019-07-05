<?php
namespace app\index\controller;
use think\Controller;
use \Cache;
use think\Db;
use think\facade\Request;
use think\facade\Session;
class Login  extends controller
{
    public function index()
    { 
    if ( Request::isPost() ){
    $data = input('post.');
    if ($data!=null) {
    $res = Db::table('admin')->where($data)->find();
        if ($res) {
          Session::set('aid',$res['id']);
          Session::set('data',$res);
          $this->success('登录成功',url('Index/index'));
        }
      }else{
        $this->error('请输入登录信息',url('Login/index'));
      }
     }
    	return view('index');
    }


    public function log(){
    	$data = input('post.');
    	if ($data!=null) {
    		$res = Db::table('admin')->where($data)->find();
    		if ($res) {
    			Session::set('aid',$res['id']);
    			Session::set('data',$res);
    			$this->success('登录成功',url('Index/index'));
    		}
    	}else{
    		$this->error('请输入登录信息',url('Login/index'));
    	}
    	
    }
   	
   	public function logout(){
   		session(null);
   		$this->success('退出登录',url('Login/log'));
   	}

}
