<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {

    	return view();
    }
    public function add(){

    	$this->success('111',url('index/hello'));
    }

    public function hello($name = 'ThinkPHP5')
    {
        return '第三次--嘉俊测试,' . $name;
    }
}
