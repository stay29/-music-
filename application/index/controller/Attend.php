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
        
        $time = time();
        $today = date('Y-m-d');
        $record = db('staff_attend')->where(['t_id'=>$uid, 'work_date'=>$today])->find();
        $data = ['t_id' => $uid];
        if(!$record){
            $data['work_day'] = $today;
            $data['on_time'] = $time;
            
            db('staff_attend')->insert($data);
        }elseif(!empty($record['on_time']) && empty($record['off_time'])){
            
            
        }else{
           $this->returnError('40000', '您上下班已打卡');  
        }
        
        
    }
    
    //打卡
    public function mark_down(){
        
    }
    
    //学生打卡
    public function todo(){
        
    }
}

