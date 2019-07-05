<?php
namespace app\index\controller;
use app\index\model\Erp2Curriculums;
use think\Controller;
use think\Db;
use think\Request;
use think\facade\Session;
class Curriculums extends controller
{   
    public function ces(){
    //$res =  Erp2Curriculums::field('cur_id,cur_name,create_time')->select();
    //$res = new Erp2Curriculums;
    //$a =  $res->addc($data);
   
    //print_r($res);
    return  view();
    }

    public function ceson(){
       $data = input('post.');
       print_r($data);


       
    }
	//添加修改课程
    public function addcurrlums()
    {  
       $data = input('post.');
       $cur_id['cur_id'] = input('post.cur_id');
       if($cur_id['cur_id']!=null){
       	$res = Db::name('erp2_curriculums')->where($cur_id)->update($data);
       }else{
       	$res = Db::name('erp2_curriculums')->data($data)->insert();
       }
       if($res){
          return_data(1,1);
       }else{
    	  return_data(0,2);
       }
    }
    //删除课程
    public function delcurrlums(){
    	$data = input('post.');
    	$res = Db::name('erp2_curriculums')->where($data)->delete();
    	if($res){
          return_data(1,1);
        }else{
    	  return_data(0,2);
        }	
    }

    //课程列表
    public function currlumslist(){
    	$data = input('post.');
    	$res = Db::name('erp2_curriculums')->where($data)->select();
    	return_data(1,1,$res);
    }





}
