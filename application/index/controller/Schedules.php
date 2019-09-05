<?php


namespace app\index\controller;
use app\index\model\Schedule;
use think\Db;
use think\Exception;
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
   /*
    * 获得学生待排课
    */
   public function  get_ready_arrange_cur(){

   }
   /*
    * 获得待排课的学生列表
    */
   public function  get_ready_arrange_stu(){

   }
}