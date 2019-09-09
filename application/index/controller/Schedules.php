<?php


namespace app\index\controller;
use app\index\model\Purchase_Lessons as Plessons;
use app\index\model\Schedule;
use think\Db;
use think\Exception;
use think\facade\Request;

class Schedules extends BaseController
{
    /*
     * 老师排课
     */
    public function add_teacher_schedule(){

        $data=input('post.');
        // validate data.
        $validate=new Schedule();
        if(!$validate->scene('add')->check($data)){
//            var_dump($validate->getError());
            $error = explode('|', $validate->getError());
//            var_dump($error);
            $this->return_data(0, $error[1], $error[0]);
        }
        Db::startTrans();
        try{
            Schedule::create($data);
            return $this->returnData('',"排课成功");
        }catch (Exception $exception){
            Db::rollback();
        }


    }
   /**
    * 获得学生待排课
    */
   public function  get_ready_arrange_cur(){

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
           ->join('erp2_teachers d ','d.t_id=a.t_id')
           ->join('erp2_students b','a.stu_id=b.stu_id')
           ->join('erp2_curriculums c','c.cur_id=a.cur_id')
           ->field('a.stu_id,c.cur_name,b.truename,c.tmethods,original_price,a.class_hour');
       if(isset($where)){
           $data=$data ->where($where);
       }
          $data=$data  ->select();
//           ->where(['d.t_name LIKE '.'%'.$tea_name.'%','b.truename LIKE '.'%'.$stu_name.'%','c.cur_name LIKE'.'%'.$cur_name.'%'])

       $this->returnData($data,"");
   }
}