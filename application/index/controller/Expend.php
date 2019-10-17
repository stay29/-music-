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
        $org_id = ret_session_name('orgid');
        $name = input('name/s', ''); // 杂项名称
        $type_id = input('type_id', ''); // 支出类型
        $start_time = input('start_time/d', '');  //开始时间
        $end_time = input('end_time/d', '');  //结束时间
        $limit = input('limit/d', 20);
        $page = input('page/d', 1);
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try{
            $teacher = db('expend_log')->alias('el')->field('el.id, el.name, el.amount, el.mark_time, el.pay_time, us.nickname, el.remark');
            $teacher->where('org_id', '=', $org_id);
            if($name !== null)
            {
                $teacher->where('name', 'like', '%' . $t_name . '%');
            }

            if(!empty($type_id))
            {
                $teacher->where('type_id', '=', $type_id);
            }
            if(!empty($start_time) && !empty($end_time))
            {
                $teacher->where('mark_time', 'between time', [$start_time, $end_time]); 
            }
            
            $data = $teacher->order('mark_time_time DESC')
                            ->leftJoin('erp2_users us', 'us.uid=el.manager')
                            ->paginate($limit, false, ['page' => $page]);
            
            $response = [
                'last_page' => $data->lastPage(),
                'per_page' => $data->listRows(),
                'total' => $data->total(),
                'data' => $data->items()
                ];

            $this->returnData($response, '请求成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
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
        if(empty($name)){
            $this->returnError(40000, '名字不能为空');
        }
        $data = [
            'type_name' => $name,
            'order_num' => $order,
            'org_id' => $org_id
        ];
        try{
            db('expend_type')->insert($data);
            $this->returnData(1,'新增成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }
    
    //修改支出类型
    public function type_edit() {
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $org_id = ret_session_name('orgid');
        $id = input('t_id/d', '');
        if (!$org_id || !$id)
        {
            $this->returnError('10000', '缺少参数');
        }
        $data = [
            'type_name' => $name,
            'order_num' => $order,
        ];
        try{
           db('expend_type')->where(['id' => $id, 'org_id' => $org_id])->update($data);
           $this->returnData(1,'修改成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        } 
    }
    
    //删除支出类型
    public function type_del() {
        $t_id = input('t_id/d', '');
        $org_id = ret_session_name('orgid');
        if(!$t_id || !$org_id){
           $this->returnError('10000', '缺少参数');
        }
        try{
           db('expend_type')->where(['org_id' => $org_id, 'id' => $t_id])->delete();
           $this->returnData(1,'删除成功');
        }catch (\Exception $e){
             $this->returnError(50000, '服务器错误');
         } 
    }
    
    //添加支出明细
    public function add() {
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $org_id = ret_session_name('orgid');
        $type_id = input('type_id/d', '');
        $name = input('name/s', '');
        $pay_time = input('pay_time', '');
        $amount = input('amount');
        $remark = input('remark', '');
        if(empty($org_id)){
           $this->returnError('10000', '缺少参数'); 
        }
        if(empty($type_id) || empty($name) || empty($pay_time) || !isset($amount)){
            $this->returnError('10000', '必填项不能为空');
        }
        $data = [
            'org_id' => $org_id,
            'type_id' => $type_id,
            'name' => $name,
            'pay_time' => $pay_time, 
            'amount' => $amount,
            'remark' => $remark
        ];
        try{
            db('expend_log')->insert($data);
            $this->returnData(1,'新增成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }


    
    //删除支出明细
    public function del() {
        $e_id = input('eid/d', '');
        $org_id = ret_session_name('orgid');
        if(!$e_id || !$org_id){
           $this->returnError('10000', '缺少参数'); 
        }
        try{
           db('expend_log')->where(['org_id' => $org_id, 'id' => $e_id])->delete();
           $this->returnData(1,'删除成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }
    
}
