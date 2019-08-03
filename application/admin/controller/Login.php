<?php
namespace app\admin\controller;

class Login extends AdminBase
{ 
     protected $beforeActionList = [
        'first',//验证有没有登录了
        // 'second' =>  ['except'=>'hello'],
        // 'three'  =>  ['only'=>'hello,data'],
    ];

    public function first(){
        if(session('?admin')){
            $this->redirect(url('admin/index/index'),302);
        }
    }

    private function after(){
          db('admins')
              ->where(['id'=>session('admin.id')])
              ->update(['ad_login_time'=>time()]);
    }
    public function index()
    {
        return $this->fetch();
    }

    public function login(){
    	$ac = input('ac');
    	$pwd = input('pwd');
        $has = db('admins')
            ->where(['ad_account'=>$ac])
            ->field('ad_status,ad_secret')
            ->find();
        if(!$has['ad_secret']){
            $this->return_data(0,'输入账号不存在');
        }
        if($has['ad_status'] == 2){
             $this->return_data(0,'账号已被禁用，请联系管理员');
        }
        $log = db('admins')
        ->where(['ad_account'=>$ac,'ad_password'=>$pwd])
        ->field('id,ad_account,ad_login_time,ad_create_time,ad_update_time,ad_nickname')
        ->find();
        session('admin',$log);
    	if($log){
    		$info = '登录成功';
            $status = 1;
            $this->after();
    	}else{
    		$info = '验证错误';
            $status = 0;
    	}
    	$this->return_data($status,$info);
    }
     
}
