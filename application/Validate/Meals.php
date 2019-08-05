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
       // 'remarks' =>     'require',
        'meals_cur'=>    'require',
        'list_img' =>    'require',
        'bg_img' =>      'require',
        'orgid' =>       'require',
        'manager' =>     'require',
      
    ];

    protected $message = [
        'meal_name.require'=>'套餐名称不能为空|10000',
        'meal_name.max'=>'套餐名称最长12个字|10001',
        'value.require'=>'套餐价值不能为空|10000',
        'value.number'=>'套餐价值必须为数字|10001',
        'price.require'=>'套餐金额不能为空|10000',
        'price.number'=>'套餐金额必须为数字|10001',
        'cur_state.require'=>'启动套餐不能为空|10000',
        'cur_state.number'=>'启动套餐必须为数字|10001',
       // 'remarks.require'=>'备注不能为空|10000',
        'meals_cur.require'=>'课程ID不能为空|10000',

        'list_img.require'=>'列表图不能为空|10000',

        'bg_img.require'=> '详情图不能为空|10000',

        'orgid.require'  =>  '机构不能为空|10000',
        'manager.require'=>  '用户id不能为空|10000',
        'cur_id.require' =>  '套餐课程id不能为空|10000',
    ];

    public function sceneAdd()
    {
        return $this->only(['cur_id','meal_name','value','price','cur_state','remarks','meals_cur','list_img','bg_img','orgid','manager']);
    }

    public function sceneAddtow()
    {
        return $this->only(['cur_id','meal_name','value','price','cur_state','meals_cur','orgid','manager']);
    }
    public function sceneEdit()
    {
        return $this->only(['meal_id','meal_name','value','price','cur_state','remarks','meals_cur','list_img','bg_img','orgid','manager']);
    }
}