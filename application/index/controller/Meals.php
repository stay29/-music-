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
use app\index\model\Meals as Mealss;
use app\index\model\MealCurRelations;
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
                $this->return_data(1,0,'添加成功');
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }

     //添加套餐课程返回套餐课程id
     public  function addmealcur(){
        $data  = [
            'cur_name'=>input('post.meal_name'),
            'cur_num'=>input('post.meal_name'),
            'meal_name'=>input('post.meal_name'),
            'cur_id'=>input('post.cur_id'),
            'valid_day'=>input('post.valid_day'),
            'cur_value'=>input('post.cur_value'),
            'actual_price'=>input('post.actual_price'),
        ];
        $res = MealCurRelations::create($data);
        return $res->meal_cur_id;
     }





}