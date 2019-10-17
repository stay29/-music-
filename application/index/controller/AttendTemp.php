<?php

namespace app\index\controller;

use think\Controller;
use think\Exception;

/*
 * 考勤模板管理
 */
class AttendTemp extends BaseController
{
    //模板列表
    public function index(){
        $org_id = ret_session_name('orgid');
        $limit = input('limit/d', 20);
        $page = input('page/d', 1);
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try{
            $temp = db('attend_temp')->alias('at')->field('at.id, at.temp_name, at.create_time, at.manager, usr.nickname');
            $temp->where('org_id', '=', $org_id);

            $data = $temp->leftJoin('erp2_users usr', 'usr.uid=at.manager')
                        ->order('create_time DESC')
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
    
    //添加模板
    public  function add(){
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $org_id = ret_session_name('orgid');
        if (!$org_id)
        {
            $this->returnError('10000', '缺少参数');
        }
        $data = [
            'temp_name' => input('post.temp_name/s', ''),
            'org_id' => $org_id,
            'mon_start' => input('post.mon_start', ''),
            'mon_end' => input('post.mon_end', ''),
            'tue_start' => input('post.tue_start', ''),
            'tue_end' => input('post.tue_end', ''),
            'wed_start' => input('post.wed_start', ''),
            'wed_end' => input('post.wed_end', ''),
            'thu_start' => input('post.thu_start', ''),
            'thu_end' => input('post.thu_end', ''),
            'fri_start' => input('post.fri_start', ''),
            'fri_end' => input('post.fri_end', ''),
            'sat_start' => input('post.sat_start', ''),
            'sat_end' => input('post.sat_end', ''),
            'sun_start' => input('post.sun_start', ''),
            'sun_end' => input('post.sun_end', ''),
            'be_late' => input('post.late/d', ''),
            'be_leave' => input('post.early/d', ''),
            'create_time' => time(),
            'manager' => ret_session_name('uid')
        ];
        
        try{
           db('attend_temp')->insert($data);
           $this->returnData(1,'模板新增成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }
    
    //修改模板
    public  function edit(){
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $org_id = ret_session_name('orgid');
        $id = input('at_id/d', '');
        if (!$org_id  || !$id)
        {
            $this->returnError('10000', '缺少参数');
        }
        $data = [
            'temp_name' => input('post.temp_name/s', ''),
            'mon_start' => input('post.mon_start', ''),
            'mon_end' => input('post.mon_end', ''),
            'tue_start' => input('post.tue_start', ''),
            'tue_end' => input('post.tue_end', ''),
            'wed_start' => input('post.wed_start', ''),
            'wed_end' => input('post.wed_end', ''),
            'thu_start' => input('post.thu_start', ''),
            'thu_end' => input('post.thu_end', ''),
            'fri_start' => input('post.fri_start', ''),
            'fri_end' => input('post.fri_end', ''),
            'sat_start' => input('post.sat_start', ''),
            'sat_end' => input('post.sat_end', ''),
            'sun_start' => input('post.sun_start', ''),
            'sun_end' => input('post.sun_end', ''),
            'be_late' => input('post.late/d', ''),
            'be_leave' => input('post.early/d', '')
        ];
        
        try{
           db('attend_temp')->where(['id' => $id, 'org_id' => $org_id])->update($data);
           $this->returnData(1,'模板修改成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }
    
    //删除模板
    public  function del(){
        $id = input('at_id/d', '');
        $org_id = ret_session_name('orgid');
        if(!$id || !$org_id){
           $this->returnError('10000', '缺少参数'); 
        }
        try{
           db('attend_temp')->where(['org_id' => $org_id, 'id' => $id])->delete();
           $this->returnData(1,'删除成功');
        }catch (\Exception $e){
             $this->returnError(50000, '服务器错误');
         }
    }
    
    //设置为默认模板
    public  function set_default(){
        $id = input('at_id/d', '');
        $org_id = ret_session_name('orgid');
        if(!$id || !$org_id){
           $this->returnError('10000', '缺少参数'); 
        }
        try{
           db('attend_temp')->where(['org_id' => $org_id])->update(['is_default' => 0]);
           db('attend_temp')->where(['org_id' => $org_id, 'id' => $id])->update(['is_default' => 1]);
           $this->returnData(1,'设置成功');
        }catch (\Exception $e){
             $this->returnError(50000, '服务器错误');
         }
    }
}
