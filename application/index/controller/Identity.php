<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use Think\Exception;

/*
 * 身份管理
 */
class Identity extends BaseController
{
    public function index() {
        $org_id = ret_session_name('orgid');
        if(empty($org_id)){
           $this->returnError('5000', '缺少参数'); 
        }
        $res = db('identity')->field('id, identity_name')->where('org_id', '=', $org_id)->order('id desc')->select();
        $this->returnData($res, '查询成功');
    }
    
    //添加身份
    public function add() {
        $name = input('name/s', '');
        $org_id = input('orgid/d', '');
        if(empty($org_id)){
           $this->returnError('5000', '缺少参数'); 
        }
        if(empty($name)){
           $this->returnError('5000', '身份名称不能为空'); 
        }
        $res = db('identity')->insert(['identity_name' => $name, 'org_id' => $org_id]);
        if($res){
            $this->returnData('', '添加成功');
        }else{
            $this->returnError('5001', '添加失败');
        }
    }
    
    //修改身份
    public function edit() {
        $id = input('iden_id/d', '');
        $org_id = ret_session_name('orgid');
        $name = input('name/s', '');
        if(empty($org_id)){
           $this->returnError('5000', '缺少参数1'); 
        }
        if(empty($id)){
            $this->returnError('5000', '缺少参数2');
         }
        if(empty($name)){
            $this->returnError('5000', '身份名称不能为空'); 
         }
         $res = db('identity')->where(['id' => $id, 'org_id' => $org_id])->update(['identity_name' => $name]);
         if($res !== false){
             $this->returnData('', '修改成功');
         }else{
           $this->returnError('5001', '修改失败');  
         }
    }
    
    //删除身份
    public function del() {
        $id = input('iden_id/d', '');
        $org_id = ret_session_name('orgid');
        if(empty($org_id)){
           $this->returnError('5000', '缺少参数1'); 
        }
        if(empty($id)){
            $this->returnError('5000', '缺少参数'); 
         }
        $has = db('teachers')->where(['is_teacher' => 0, 'iden_id' => $id])->select();
        if($has){
           $this->returnError('5001', '该身份正在使用'); 
        }
        try{
            db('identity')->where(['id' => $id, 'org_id' => $org_id])->delete();
            $this->returnData('', '删除成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }
}