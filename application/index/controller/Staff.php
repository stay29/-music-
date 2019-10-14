<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use Think\Exception;

/*
 * 员工管理
 */
class Staff extends BaseController
{
    //主管要求员工并入到教师表中操作，is_teacher默认为1教师,0员工。0时需填入iden_id身份id
    //员工列表
    public function index(){
        $org_id = input('orgid', '');
        $t_name = input('t_name/s', ''); // 员工名称
        $iden_id = input('iden_id', ''); // 身份ID
        $status = input('status/d', '');  // 离职状态
        $limit = input('limit/d', 20);
        $page = input('page/d', 1);
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try{
            $teacher = db('teachers')->field('t_id as id, t_name as name, 
                    sex,cellphone,status, iden_id, is_teacher, iden_id');
            $teacher->where('org_id', '=', $org_id);
            if($t_name !== null)
            {
                $teacher->where('t_name|cellphone', 'like', '%' . $t_name . '%');
            }

            if(!empty($status))
            {
                $teacher->where('status', '=', $status);
            }
            if(!empty($iden_id))
            {
                if($iden_id == 'teacher'){
                   $teacher->where('is_teacher', '=', 1); 
                }else{
                   $teacher->where('iden_id', '=', $iden_id); 
                }
            }
            $teacher->where('is_del', '=', 0);
            
            $res = db('identity')->select();
            $ids = array_column($res, 'id');
            $iname = array_column($res, 'identity_name');
            $idens = array_combine($ids, $iname);
            $data = $teacher->order('create_time DESC')
                            ->paginate($limit, false, ['page' => $page])
                            ->each(function($log, $lk) use ($idens){
                                if($log['sex'] === 1){
                                    $log['sex_show'] = '男';
                                }else{
                                    $log['sex_show'] = '女';
                                }
                                
                                if ($log['status'] === 1) //在职
                                {
                                    $log['status_show'] = '在职';
                                }else{
                                    $log['status_show'] = '离职';
                                }
                                $iden_text = '教师';
                                if(!$log['is_teacher']){
                                   $iden_text = $idens[$log['iden_id']]; 
                                }
                                $log['iden_text'] = $iden_text;
                                return $log;
                            });
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
    
    //增加员工
    public function add(){
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $org_id = input('orgid/d', '');
        if (!$org_id)
        {
            $this->returnError('50000', '缺少参数');
        }
        $data = [
            't_name' => input('post.t_name/s', ''),
            'sex' => input('post.sex/d',1),
            'is_teacher' => 0,
            'iden_id' => input('post.iden_id', 0),
            'cellphone' => input('post.cellphone/s', ''),
            'org_id' => $org_id
        ];
        try{
           db('teachers')->where(['cellphone='=> $data['cellphone'], 'org_id' => $data['org_id']])->find();
           $this->returnError(50000, '该手机号码已有员工使用');
           db('teachers')->insert($data);
           $this->returnData(1,'员工新增成功');
        }catch (\Exception $e){
            $this->returnError(50000, $e->getMessage());
        }
    }
    
    //修改员工
    public function edit(){
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $org_id = input('orgid/d', '');
        $id = input('t_id/d', '');
        if (!$org_id || !$id)
        {
            $this->returnError('10000', '缺少参数');
        }
        $data = [
            't_name' => input('post.t_name/s', ''),
            'sex' => input('post.sex/d',1),
            'iden_id' => input('post.iden_id', 0),
            'cellphone' => input('post.cellphone/s', '')
        ];
        try{
           db('teachers')->where(['t_id' => $id, 'org_id' => $org_id])->update($data);
           $this->returnData(1,'修改成功');
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    } 
    
    //删除员工
    public function del(){
       $t_id = input('t_id/d', '');
       $org_id = input('orgid/d', '');
       if(!$t_id || !$org_id){
          $this->returnError('10000', '缺少参数'); 
       }
       try{
          db('teachers')->where(['is_teacher'=>0, 'org_id' => $org_id, 't_id' => $t_id])->delete();
          $this->returnData(1,'删除成功');
       }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }
    
    //员工在离职转换
    public function change(){
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $id = input('t_id/d', '');
        $status = input('status', '');
        if (!$status || !$id)
        {
            $this->returnError('10000', '缺少参数');
        }
        try{
            $res = db('teachers')->where('t_id', '=', $id)->update(['status'=>$status]);
            if($res)
            {
                $this->returnData('', '操作成功');
            }
            else
            {
                $this->returnError(20003, '不能改为现在状态');
            }
        }catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    } 
}

