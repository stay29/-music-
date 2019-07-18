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
    public static  function  addmeals($data)
    {
        $res = Meals::create($data);
        return $res;
    }
}