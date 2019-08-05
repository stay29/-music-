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
            'manager'=>input('post.manager'),
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
//         $data = array(
//             array(
//                 'cur_id'=>'561',
//                 'cur_name'=>'css1',
//                 'cur_num'=>'',
//                 'cur_value'=>'222',
//                 'actual_price'=>'222',
//                 'course_model'=>'2',
//             ),
//             array(
//                 'cur_id'=>'551',
//                 'cur_name'=>'css1',
//                 'cur_num'=>'5',
//                 'cur_value'=>'222',
//                 'actual_price'=>'222',
//                 'course_model'=>'2',
//             ),
//         );
         $res = array();
         $validate = new \app\validate\MealCurRelations;
         Db::startTrans();
         try{
         foreach ($data as $k=>&$v){
             if(!$validate->scene('add')->check($v)){
                 //为了可以得到错误码
                 $error = explode('|',$validate->getError());
                 $this->return_data(0,$error[1],$error[0]);
                 exit();
             }else{
                 $res[] = Mclmodel::create($v);
             }
             Db::commit();
         }
         }catch (\Exception $e){
             Db::rollback();
             $this->return_data(0,50000,$e->getMessage());
         }
         foreach ($res as $ks=>&$vs){
             $resid[] = $vs['id'];
         }
         //$resid = implode(',',$resid);
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
        $cur_state = input('cur_state');
        $orgid  =input('orgid');
        $where = null;
        if($meal_name){
            $where[]=['meal_name','like','%'.$meal_name.'%'];
        }
        if($cur_state){
            $where[]=['cur_state','=',$cur_state];
        }
        $where[] = ['orgid','=',$orgid];
        try{
            $res = Mealss::getall($limit,$where);
            $this->return_data(1,0,'套餐列表',$res);
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }
    //套餐修改
    public  function  editmealss()
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
            'manager'=>input('post.uid'),
            'update_time' =>time(),
        ];
        $mid = input('post.meal_id');
        Db::startTrans();
        try{
            $validate = new \app\validate\Meals;
            if(!$validate->scene('edit')->check($data)){
                //为了可以得到错误码
                $error = explode('|',$validate->getError());
                $this->return_data(0,$error[1],$error[0]);
                exit();
            }else{
                $res = Mealss::editinfo($data,$mid);
                Db::commit();
                $this->return_data(1,0,'修改成功',$res);
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    //套餐详情
    public  function  get_meals_info()
    {
        $mid = input('post.meal_id');
        $res = Mealss::where('meal_id',$mid)->find();
        $res['meals_cur'] =  Mealss::get_mealkec_name($res['meals_cur']);
        $this->return_data(1,0,'获取详情',$res);
    }

    //修改套餐课程
    public function  edit_meals_mealcur()
    {
        $meal_cur_id = input('meal_cur_id');
        $data = [
                 'cur_id'=>input('cur_id'),
                 'cur_name'=>input('cur_name'),
                 'cur_num'=>input('cur_num'),
                 'cur_value'=>input('cur_value'),
                 'actual_price'=>input('actual_price'),
                 'course_model'=>input('course_model'),
                 'meal_cur_id'=>input('meal_cur_id'),
        ];
        Db::startTrans();
        try{
            $validate = new \app\validate\MealCurRelations;
            if(!$validate->scene('edit')->check($data)){
                //为了可以得到错误码
                $error = explode('|',$validate->getError());
                $this->return_data(0,$error[1],$error[0]);
                exit();
            }else {
                $res = Mclmodel::where('meal_cur_id', $meal_cur_id)->update($data);
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
        if($res){
            $this->return_data(1,0,'修改成功',$res);
        }else{
            $this->return_data(0,10000,'修改失败');
        }
    }

    //删除套餐课程
    public  function  del_meals_mealcur()
    {
        $meal_cur_id = input('meal_cur_id');

        //$mid = input('post.meal_id');
        Db::startTrans();
        try{
        $res = Mclmodel::where('meal_cur_id',$meal_cur_id)->delete();
            Db::commit();
        if($res){
            //$info = Mealss::where('meal_id',$mid)->find();
            //删除课程套餐后把链接的套餐里的id删除
            //$a['meals_cur'] =  str_replace($meal_cur_id.',',"",$info['meals_cur']);
            //$info_res =Mealss::where('meal_id',$mid)->update($a);
            $this->return_data(1,0,'删除成功',$res);
        }else{
            $this->return_data(0,10000,'删除失败',$res);
        }
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }



}