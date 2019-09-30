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
        $se_id = input('se_id/d', ''); // 资历ID
        $status = input('status/d', '');  // 离职状态
        $limit = input('limit/d', 20);
        $page = input('page/d', 1);
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try{
            $teacher = db('teachers')->where('org_id', '=', $org_id)->alias("a");
            if(!empty($t_name) || $t_name==0)
            {
                $teacher->where('t_name', 'like', '%' . $t_name . '%');
            }

            if(!empty($status))
            {
                $teacher->where('status', '=', $status);
            }
            $teacher->where('a.is_del', '=', 0);

            $data = $teacher->field('a.t_id as id,a.t_name as name, a.avator,
                    a.sex,a.cellphone,a.entry_time,a.status, a.se_id, a.resume')->order('create_time DESC')
                                ->paginate($limit, false, ['page' => $page])
                                ->each(function($log, $lk){
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
        $data = [
            't_name' => input('post.t_name/s', ''),
            'avator' => input('post.avator/s', ''),
            'sex' => input('post.sex/d',1),
            'cellphone' => input('post.cellphone/s', ''),
            'birthday' => input('post.birthday/d', ''),
            'entry_time' => input('post.entrytime/d', ''),
            'org_id' => input('orgid/d', '')
        ];  
    }
    
    //修改员工
    public function edit(){
        
    } 
    
    //删除员工
    public function del(){
        
    } 
}

