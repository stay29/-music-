<?php
namespace app\index\controller;
use think\Controller;
use \Cache;
use think\Db;
use think\facade\Request;
use think\facade\Session;
class Comm  extends controller
{
//另一种方法，使用构造函数初始化
    public function __construct(){        
        //使用父类的构造函数，也就是调用Controller类的构造函数
       parent::__construct(); 
       if(session('aid')==null){
          $this->error('请先登录',url('Login/index'));
       } 
    }
    

}
