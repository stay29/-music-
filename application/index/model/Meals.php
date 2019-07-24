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
class Meals extends Model
{

    protected $autoWriteTimestamp = true;
   // protected $insert = ['status'=>1,'popular'=>2];
    public static  function  addmeals($data)
    {

        $res = Meals::create($data);
        return $res;
    }
    //套餐列表
    public  static  function  getall($limit,$where)
    {
        $list = Meals::where($where)
        ->paginate($limit);
        return $list;
    }


}
