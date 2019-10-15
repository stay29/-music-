<?php
namespace app\index\controller;

use app\index\controller\BaseController;
use think\Exception;

/*
 * 员工考勤管理
 */
class Attend extends BaseController
{
    //员工考勤情况
    public function index(){
        $name = input('name/s', '');
        $status = input('status/d', '');
        $org_id = ret_session_name('orgid');
        $limit = input('limit/d', 20);
        $page = input('page/d', 1);
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try{
            $teacher = db('staff_attend')->alias('sa')->field('sa.*, t.t_name, t.is_teacher, t.iden_id, sf.identity_name');
            $teacher->where('t.org_id', '=', $org_id);
            $teacher->where('sf.org_id', '=', $org_id);
            if($name !== null)
            {
                $teacher->where('t.t_name', 'like', '%' . $name . '%');
            }

            if(!empty($status))
            {
                $teacher->where('sa.status', '=', $status);
            }

            $data = $teacher->order('sa.id DESC')
                            ->leftJoin('erp2_teachers t', 't.t_id=sa.t_id')
                            ->leftJoin('erp2_identity sf', 'sf.id=t.iden_id')
                            ->paginate($limit, false, ['page' => $page])
                            ->each(function($log, $lk) use ($idens){
                                if($log['is_teacher'] === 1){
                                    $log['identity_name'] = '教师';
                                }
                                
                                if ($log['status'] === 1) //在职
                                {
                                    $log['status_show'] = '迟到';
                                }elseif ($log['status'] === 2){
                                    $log['status_show'] = '早退';
                                }elseif ($log['status'] === 3){
                                    $log['status_show'] = '迟到早退';
                                }else{
                                    $log['status_show'] = '迟到早退'; 
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
    
    //打卡  status  0正常 1迟到 2早退 3迟到早退 4缺卡
    public function mark_down(){
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
        }
        $uid = input('uid', '');
        $org_id = input('orgid', '');
        if(empty($uid) || empty($org_id)){
            $this->returnError('40000', '缺少参数'); 
        }
        
        //机构的默认考勤模板
        $temp = db('attend_temp')->where(['org_id' => $org_id, 'is_default' => 1])->find();
        if(! $temp){
           $this->returnError('40000', '请设置默认考勤模板'); 
        }
        $on_temp = array('sun_start', 'mon_start', 'tue_start', 'wed_start', 'thu_start', 'fri_start', 'sat_start');
        $off_temp = array('sun_end', 'mon_end', 'tue_start', 'wed_end', 'thu_end', 'fri_end', 'sat_end');
        
        $time_key = date('w');
        $time = time();
        $today = date('Y-m-d');
        $record = db('staff_attend')->where(['t_id'=>$uid, 'work_date'=>$today])->find();
        $data = ['t_id' => $uid];
        $msg = '';
        try{
            if(!$record){
                $data['work_day'] = $today;
                $data['on_time'] = $time;
                $stand = $temp[$on_temp[$time_key]];
                if($time > ($stand + $temp['be_late'] * 60)){
                    $date['status']  = 1;
                    $msg = '，您今天迟到了';
                }
                db('staff_attend')->insert($data);
                $this->returnData($res, '打卡成功'.$msg);
            }elseif(!empty($record['on_time']) && empty($record['off_time'])){
                    $data['off_time'] = $time;
                    $stand = $temp[$off_temp[$time_key]];
                    if($time < ($stand - $temp['be_leave'] * 60)){
                        $date['status']  = 2;
                        if($record['status'] === 1){
                            $date['status']  = 3;
                        }
                        $msg = '，您今天早退了';
                    }
                    db('staff_attend')->where('id', '=', $record['id'])->update($data);
                    $this->returnData($res, '打卡成功'.$msg);
            }else{
               $this->returnError('40000', '您上下班已打卡');  
            }
        } catch (\Exception $e){
            $this->returnError(50000, '服务器错误');
        }
    }
    
    //学生打卡
    public function todo(){
        
    }
}

