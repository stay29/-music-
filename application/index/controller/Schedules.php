<?php


namespace app\index\controller;
use app\index\model\Purchase_Lessons as Plessons;
use app\index\model\Schedule;
use app\index\model\TSchedulesHistory;
use think\Db;
use think\Exception;
use think\facade\Request;

class Schedules extends BaseController
{
    /*
     * 老师排课
     */
    public function add_teacher_schedule(){
        $or_id= Request::instance()->header()['orgid'];  //从header里面拿orgid
        $data=input('post.');
        $data['or_id']=$or_id;
        Db::startTrans();
        try{
        $history=  TSchedulesHistory::create($data);
        // validate data.
//        $validate=new Schedule();
//        if(!$validate->scene('add')->check($data)){
////            var_dump($validate->getError());
//            $error = explode('|', $validate->getError());
////            var_dump($error);
//            $this->return_data(0, $error[1], $error[0]);
//        }
        $start_time=input('post.start_time');
        $pitch_num=input('post.pitch_num');
        $type=input('post.type');
        //将开始时间移到周几开始的时间
        $sub=date('w',$start_time)-input('post.day');
        if($sub>0){
            $start_time+=(7-$sub)*24*60*60;
        }else{
            $start_time+=$sub*24*60*60;
        }
        //for循环一节一节添加
        for ($n=0;$n<$pitch_num;$n++){
            $schedule=[
//                'stu_id'=>input('post.stu_id'),
//                't_id'=>input('post.t_id'),
//                'room_id'=>input('post.room_id'),
//                'cur_id'=>input('post.cur_id'),
//                'type'=>input('post.c_type'),
                'org_id'=>$or_id,
                'th_id'=>$history['id']
                ];
            //3种排课类型相隔天数不同
            switch ($type){
                case 0:   //每天
                    $schedule['cur_time']=$start_time+input('post.day_time')+$n*24*60*60;
                    break;
                case 1:  //每周
                    $schedule['cur_time']=$start_time+input('post.day_time')+$n*24*60*60*7;
                    break;
                case 2:  //隔周
                    $schedule['cur_time']=$start_time+input('post.day_time')+$n*24*60*60*7*2;
                    break;
            }
            Schedule::create($schedule);
        }

            Db::commit();
            return $this->returnData('',"排课成功");
        }catch (Exception $exception){
            Db::rollback();
            $this->returnError("",$exception->getMessage());
        }


    }
   /**
    * 获得学生待排课
    */
   public function  get_ready_arrange_cur(){
//       $stu_id=Plessons::
   }
   /**
    * 获得待排课的列表
    * @param  stu_name学生姓名
    * @param tea_name 老师姓名
    *@param  cur_name 课程姓名
    */
   public function  get_ready_arrange_stu(){
       $or_id= Request::instance()->header()['orgid'];  //从header里面拿orgid
       $data=null;
       $stu_name=input('get.stu_name');
       $cur_name=input('get.cur_name');
       $tea_name=input('get.tea_name');
       $limit=input('get.limit',20);
       if($tea_name){
         $where[]=  ['d.t_name','like','%'.$tea_name.'%'];
       }
       if($stu_name){
           $where[]=['b.truename','like','%'.$stu_name.'%'];
       }
       if($cur_name){
           $where[]=['c.cur_name','like','%'.$cur_name.'%'];
       }
       $data= Plessons::where('or_id',$or_id)->alias('a')
//           ->join('erp2_teachers d ','d.t_id=a.t_id')
           ->join('erp2_students b','a.stu_id=b.stu_id')
           ->join('erp2_curriculums c','c.cur_id=a.cur_id')
           ->field('a.id,c.cur_name,b.truename,c.tmethods,original_price,a.class_hour');
       if(isset($where)){
           $data=$data ->where($where);
       }
       $data=$data  ->paginate($limit);
       $this->returnData($data,"");
   }
}