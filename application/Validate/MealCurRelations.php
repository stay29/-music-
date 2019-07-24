<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/24
 * Time: 16:21
 */
namespace app\validate;
use think\Validate;
class MealCurRelations  extends Validate
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
        'orgid' =>'require'
    ];

    protected $message = [
        'meal_name.require'=>'套餐名称不能为空|10000',
        'meal_name.max'=>'套餐名称最长12个字|10001',

        'value.require'=>'套餐名称不能为空|10000',
        'value.number'=>'参数必须为数字|10001',

        'price.require'=>'套餐名称不能为空|10000',
        'price.number'=>'参数必须为数字|10001',

        'cur_state.require'=>'套餐名称不能为空|10000',
        'cur_state.number'=>'参数必须为数字|10001',

        'remarks.require'=>'套餐名称不能为空|10000',

        'meals_cur.require'=>'套餐名称不能为空|10000',

        'list_img.require'=>'套餐名称不能为空|10000',

        'bg_img.require'=> '套餐名称不能为空|10000',
        'orgid.require'=>  '机构不能为空|10000',
    ];

    public function sceneAdd()
    {
        return $this->only(['meal_name','value','price','cur_state','remarks','meals_cur','list_img','bg_img','orgid']);
    }
}