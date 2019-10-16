<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use Think\Exception;

/*
 * 支出管理
 */
class Expend extends BaseController
{
    //获取所有支出类型
    public function type() {
        $res = db('expend_type')->order('order_num desc')->select();
        $this->returnData($res, '请求成功');
    }
    
    
    //支出数据列表
    public function index() {
        
    }
    
    //添加支出类型
    public function type_add() {
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $name = input('name/s', '');
        $org_id = ret_session_name('orgid');
        $order = input('order', '');
        
    }
    
    //修改支出类型
    public function type_edit() {
        
    }
    
    //删除支出类型
    public function type_del() {
        
    }
    
        //添加支出类型
    public function add() {
        
    }


    
    //删除支出类型
    public function del() {
        
    }
    
}
