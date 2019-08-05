<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 14:16
 */
namespace app\index\model;
use think\Model;
use think\Db;
use app\index\model\MealCurRelations as MealCurkec;
use app\index\model\Curriculums;
class Meals extends Model
{

    protected $autoWriteTimestamp = true;

    protected $insert = ['status'=>2];
    public static  function  addmeals($data)
    {

        $res = Meals::create($data);
        return $res;
    }

    public static function  get_all_list($where)
    {
        $list = Meals::where($where)->order('meal_id desc')->select()->each(function($item, $key){
            $meals_cur = Meals::get_mealkec_name($item['meals_cur']);
            foreach ($meals_cur as $k=>$v){
                $meals_cur_name[] = $v['cur_name'];
                $cur_num[] = $v['cur_num'];
                $cur_value[] = $v['cur_value'];
                $actual_price[] = $v['actual_price'];
                $course_model[] = $v['course_model'];
            }
            $item['meals_cur'] =trim(implode(',',$meals_cur_name),',');
            $item['cur_num'] =trim(implode(',',$cur_num),',');
            $item['cur_value'] =trim(implode(',',$cur_value),',');
            $item['actual_price'] =trim(implode(',',$actual_price),',');
            $item['course_model'] =trim(implode(',',$course_model),',');
        });
        return $list;
    }


    public  static  function  getall($limit,$where)
    {
        $list = Meals::where($where)->order('meal_id desc')->paginate($limit)->each(function($item, $key){
            $meals_cur = Meals::get_mealkec_name($item['meals_cur']);
            foreach ($meals_cur as $k=>$v){
                $meals_cur_name[] = $v['cur_name'];
            }
            $item['meals_cur'] =trim(implode(',',$meals_cur_name),',');
        });
        return $list;
    }

    public static  function  get_mealkec_name($arr){
      $arrinfo = explode('/',trim($arr,"/"));
      $info = array();
      //print_r($arrinfo);exit();
      foreach ($arrinfo as $k=>&$v)
      {
            $info[] = MealCurkec::where('meal_cur_id',$v)->find();
      }
        return $info;
    }



    public  static function  editinfo($data,$mid)
    {
        $list = Meals::where('meal_id', $mid)
            ->update($data);
        return $list;
    }


}
