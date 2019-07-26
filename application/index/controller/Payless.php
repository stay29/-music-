<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 13:36
 */
namespace app\index\controller;
use think\Collection;
use think\Db;
use app\index\model\PayList;
use app\validate\PayList as pays;//同名会引起报错 启用别名
class Payless extends  BaseController
{

public  function  addpayinfo()
{
    $data = [

        'cur_id'   =>   input('cur_id'),
        'cur_name'   =>   input('cur_name'),
        'pless_id'   =>   input('pless_id'),
        'p_id'   =>   input('p_id'),
        'remake'   =>   input('remake'),
        'bay_paich'   =>   input('bay_paich'),
        'orgid'   =>   input('orgid'),
        'manager'   =>   input('uid'),
    ];
    //验证器验证
    Db::startTrans();
    try {
    $validate = new pays;
    if(!$validate->scene('add')->check($data)) {
        $error = explode('|',$validate->getError());
        $this->return_data(0,$error[1],$error[0]);
        exit();
    }else{
         $res = PayList::create($data);
        // 提交事务
         Db::commit();
         if($res){
             $this->return_data(1,0,'添加成功',$res);
         }else{
             $this->return_data(0,10000,'添加失败',$res);
         }
    }
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        $this->return_data(0,50000,'添加失败',$e->getMessage());
    }
}
//删除课程薪酬
public  function  del_pay_list()
{
    $data = [
        'pay_id_list'   =>   input('pay_id_list'),
        'orgid'   =>   input('orgid'),
        'manager'   =>   input('uid'),
    ];
    $validate = new pays;
    if(!$validate->scene('del')->check($data)) {
        $error = explode('|',$validate->getError());
        $this->return_data(0,$error[1],$error[0]);
        exit();
    }else {
        $res = PayList::where($data)->delete();
        if($res){
            $this->return_data(1,0,'删除成功',$res);
        }else{
            $this->return_data(0,10000,'删除失败',$res);
        }
    }
}
//薪酬列表
public  function  pay_list()
{

    $where = array();
    $where['orgid'] = input('orgid');
    $where['manager'] = input('uid');
    $subject = input('subject');
    $cur_name = input('cur_name');
    if($cur_name){
        $where[]=['cur_name','like','%'.$cur_name.'%'];
    }
    $res1 = Db::table('erp2_pay_list')
        ->alias('p')
        ->leftJoin('erp2_curriculums c','p.cur_id= c.cur_id')
        ->join('erp2_subjects s','c.subject=s.sid')
        ->select();
    print_r($res1);exit();





}





}