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
use app\index\model\Curriculums;
use app\index\model\PayInfo as payinfos;
class Payless extends  BaseController
{

//添加课程薪酬
public  function  addpayinfo()
{
    $cur_id   =    input('cur_id');
    $data = [
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
    //数据处理
    foreach ($cur_id as $k=>&$v) {
        $data['cur_id'] = $v;
        $cur_name =Curriculums::where('cur_id',$v)->find();
        $data['cur_name'] = $cur_name['cur_name'];
    if(!$validate->scene('add')->check($data)){
        $error = explode('|',$validate->getError());
        $this->return_data(0,$error[1],$error[0]);
        exit();
    }else{
         $res = PayList::create($data);
        // 提交事务
         Db::commit();
        }
        }
        if($res){
            $this->return_data(1,0,'添加成功');
        }else{
            $this->return_data(0,10000,'添加失败');
        }
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        $this->return_data(0,50000,$e->getMessage());
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
    $where['p.orgid'] = input('orgid');
    $where['p.manager'] = input('uid');
    $subject = input('subject');
    $cur_name = input('cur_name');
    if($cur_name){
        $where[]=['p.cur_name','like','%'.$cur_name.'%'];
    }
    $res = Db::table('erp2_pay_list')
        ->alias('p')
        ->leftJoin('erp2_curriculums c','p.cur_id= c.cur_id')
        ->join('erp2_subjects s','c.subject=s.sid')
        ->where($where)
        ->select();
    $res1 = array();
    if($subject){
        foreach ($res as $k=>&$v){
            if($v['subject']==$subject){
            $res1[] = $v;
            }
        }
        $this->return_data(1,0,'搜索成功',$res1);
    }else{
        $this->return_data(1,0,'搜索成功',$res);
    }
}
    //修改课程薪酬
    public  function  edit_pay_list()
    {
        $pay_id_list   =   input('pay_id_list');
        $data = input('');
        $res =  PayList::where('pay_id_list',$pay_id_list)->update($data);
        if($res){
            $this->return_data(1,0,'修改成功',$res);
        }else{
            $this->return_data(0,10000,'修改失败',$res);
        }
    }

    //课程薪酬计算方式列表
    public  function  pay_info_list()
    {
        $where['orgid'] = ret_session_name('orgid');
        $where['status'] = 1;
        $res = payinfos::where($where)->select();
        $this->return_data(1,0,$res);
    }





}