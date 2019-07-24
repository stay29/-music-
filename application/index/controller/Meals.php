<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 13:58
 */
namespace app\index\controller;
use think\Controller;
use think\Exception;
use think\Db;
use app\index\model\Meals as Mealss;
use app\index\model\MealCurRelations as Mclmodel;
class Meals extends BaseController
{

    public  function  addmeals()
    {
        $data = [
            'meal_name'=>input('post.meal_name'),
            'value'=>input('post.value'),
            'price'=>input('post.price'),
            'cur_state'=>input('post.cur_state'),
            'remarks'=>input('post.remarks'),
            'meals_cur'=>input('post.meals_cur'),
            'list_img'=>input('post.list_img'),
            'bg_img'=>input('post.bg_img'),
            'orgid'=>input('post.orgid'),
        ];
        Db::startTrans();
        try{
            $validate = new \app\validate\Meals;
            if(!$validate->scene('add')->check($data)){
                //为了可以得到错误码
                $error = explode('|',$validate->getError());
                $this->return_data(0,$error[1],$error[0]);
                exit();
            }else{
                $res = Mealss::addmeals($data);
                 Db::commit();
                session(null);
                $this->return_data(1,0,'添加成功',$res);
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }
     //添加套餐课程返回套餐课程id
     public  function addmealcur(){
        $data = input('mealcurlist');
//        $arr = array(
//            array(
//                'cur_id'=>'56',
//                'cur_name'=>'css1',
//                'cur_num'=>'1',
//                'cur_value'=>'222',
//                'actual_price'=>'222',
//                'course_model'=>'2',
//            ),
//            array(
//                'cur_id'=>'55',
//                'cur_name'=>'css1',
//                'cur_num'=>'1',
//                'cur_value'=>'222',
//                'actual_price'=>'222',
//                'course_model'=>'2',
//            ),
//        );
         $res = array();
         foreach ($data as $k=>&$v){
            $res[] = Mclmodel::create($v);
         }
         foreach ($res as $ks=>&$vs){
             $resid[] = $vs['id'];
         }
         $resid = implode(',',$resid);
         $this->return_data(1,0,'添加成功',$resid);

     }


    //套餐列表
    public function mealss_list()
    {
        $page = input('page');
        if($page==null){
            $page = 1;
        }
        $limit = input('limit');
        if ($limit==null) {
            $limit = 10;
        }
        $meal_name = input('meal_name');
        $status = input('status');
        $orgid  =input('orgid');
        $where = null;
        if($meal_name){
            $where[]=['meal_name','like','%'.$meal_name.'%'];
        }
        if($status){
            $where[]=['status','=',$status];
        }
        $where[] = ['orgid','=',$orgid];
        try{
            $res = Mealss::getall($limit,$where);
            $this->return_data(1,0,$res);
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }


}