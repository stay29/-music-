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
        $this->auth_get_token();
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


     public  function addmealcur(){
         //print_r(input('post.'));exit();
         $data = input('mealcurlist');
         $validate = new \app\validate\MealCurRelations;
         $res = [];
         Db::startTrans();
         try{
         foreach ($data as $k=>&$v){
//             if(!$validate->scene('add')->check($v)){
//                 $error = explode('|',$validate->getError());
//                 $this->return_data(0,$error[1],$error[0]);
//                }
                }
                 foreach ($data as $kk=>&$vv)
                 {
                     $iiid  =  Db::table('erp2_meal_cur_relations')->insertGetId($vv);
                     Db::commit();
                     if($iiid){
                         $res[]= $iiid;
                     }
                 }
                 if(!empty($res)){
                     $this->return_data(1,0,'添加成功',$res);
                 }else{
                     $this->return_data(0,10000,'添加失败',$res);
                 }
                 }catch (\Exception $e){
                     Db::rollback();
                     $this->return_data(0,50000,$e->getMessage());
                 }
     }
    //套餐列表
    public function mealss_list()
    {
        $this->auth_get_token();
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
        $where[] = ['is_del','=',0];
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
        $this->auth_get_token();
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
        //print_r($res['meals_cur']);exit();
        $this->return_data(1,0,'获取详情',$res);
    }

    //修改套餐课程
    public function  edit_meals_mealcur()
    {
        $this->auth_get_token();
        //print_r(input('post.'));exit();
        $meal_cur_id = input('meal_cur_id');
        $mealcurlist = input('mealcurlist');
        foreach ($mealcurlist as $k1=>&$v1)
        {
        unset($v1['meal_cur_id']);
        unset($v1['is_del']);
        unset($v1['delete_time']);
        }
        $info = finds('erp2_meals',['meal_id'=>$meal_cur_id]);
        if($info['meals_cur']!=null){
        $cur_array = explode('/',$info['meals_cur']);
            foreach ($cur_array as $k => $v) {
                $del_info = del('erp2_meal_cur_relations', ['meal_cur_id' => $v]);
            }
//            if(!$del_info){
//                $this->return_data(0,10000,'删除过去课程失败');
//            }
        }
        Db::startTrans();
        try{
            $validate = new \app\validate\MealCurRelations;
            foreach ($mealcurlist as $ks=>&$vs){
                if(!$validate->scene('addone')->check($vs)){
                    //为了可以得到错误码
                    $error = explode('|',$validate->getError());
                    $this->return_data(0,$error[1],$error[0]);
                    exit();
                }else {
                    $res[] = add('erp2_meal_cur_relations',$vs);
                }
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
        if(!empty($res)){
            $ff = edit('erp2_meals',['meal_id'=>$meal_cur_id],['meals_cur'=>implode('/',$res)]);
            if($ff){
                $vildata = [
                    'meal_name'=>input('meal_name'),
                    'value'=>input('value'),
                    'price'=>input('price'),
                    'cur_state'=>input('cur_state'),
                    'remarks'=>input('remarks'),
                    'list_img'=>input('list_img'),
                    'bg_img'=>input('bg_img'),
                    'orgid'=>input('orgid'),
                    'manager'=>input('manager'),
                ];
                $lll = edit('erp2_meals',['meal_id'=>$meal_cur_id],$vildata);
                if($lll){
                    $this->return_data(1,0,'修改成功');
                }else{
                    $this->return_data(1,10000,'没有任何改变');
                }
            }
        }else{
            $this->return_data(1,10000,'没有任何改变');
        }
    }


    //删除套餐课程
    public  function  del_meals_mealcur()
    {
        $this->auth_get_token();
        $meal_cur_id = input('meal_cur_id');
        Db::startTrans();
        try{
        $res = edit('erp2_meals',['meal_id'=>$meal_cur_id],['is_del'=>1]);
            Db::commit();
        if($res){
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