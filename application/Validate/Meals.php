<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 14:18
 */
namespace app\validate;
use think\Validate;
class Meals  extends Validate
{
    protected  $rule = [
        'meal_name'  =>  'require|max:12',
        'value' =>       'require|number',
        'price' =>       'require|number',
        'cur_state' =>   'require|number',
        'remarks' =>     'require',
        'meals_cur'=>    'require',
        'list_img' =>    'require',
        'bg_img' =>      'require',
    ];

    protected $message = [
        'cur_name.require'=>'套餐名称不能为空|10000',
        'cur_name.max'=>'套餐名称最长12个字|10001',
        'value.require'=>'套餐价值不能为空|10000',
        'value.number'=>'套餐价值必须为数字|10001',
        'price.require'=>'套餐金额不能为空|10000',
        'price.number'=>'套餐金额必须为数字|10001',
        'cur_state.require'=>'是否启动不能为空|10000',
        'cur_state.number'=>'是否启动必须为数字|10001',
        'remarks.require'=>'备注不能为空|10000',
        'meals_cur.require'=>'课程id不能为空|10000',
        'list_img.require'=>'列表图不能为空|10000',
        'bg_img.require'=>'详情图不能为空|10000',
    ];
    //场景
    protected $scene = [
        'add' =>  ['cur_name','value','price','cur_state','remarks','list_img','meals_cur','bg_img'],

    ];
}