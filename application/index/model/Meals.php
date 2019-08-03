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
    //套餐列表
    public  static  function  getall($limit,$where)
    {
        $list = Meals::where($where)->paginate($limit)->each(function($item, $key){
            $meals_cur = Meals::get_mealkec_name($item['meals_cur']);
            foreach ($meals_cur as $k=>$v){
                $meals_cur_name[] = $v['cur_name'];
            }
            $item['meals_cur'] =trim(implode(',',$meals_cur_name),',');
        });
        return $list;
    }
    public static  function  get_mealkec_name($arr){
      //$arr = '35,36,37';
      $arrinfo = explode('/',$arr);
      $info = array();
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
