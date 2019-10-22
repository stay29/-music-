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
        'meal_cur_id' =>  'require|number',
        'cur_name'  =>    'require|max:12',
        'cur_id' =>       'require|number',
        'cur_num' =>      'require|number',
        'cur_value' =>    'require|number',
        'cur_value' =>    'require',
        'actual_price' => 'require',
        'course_model' => 'require|number',
    ];

    protected $message = [
        'cur_name.require'=>'课程名称不能为空|10000',
        'cur_name.max'=>    '课程名称最长12个字|10001',


        'cur_id.require'=>'课程id不能为空|10000',
        'cur_id.number'=> '课程id必须为数字|10001',


        'cur_num.require'=>'课程次数不能为空|10000',
        'cur_num.number'=> '课程次数必须为数字|10001',


        'meal_cur_id.require'=>'套餐课程id不能为空|10000',
        'meal_cur_id.number'=> '套餐课程id必须为数字|10001',

        'cur_value.require'=>'课程价值不能为空|10000',
        //'cur_value.number'=> '课程价值必须为数字|10001',

        'actual_price.require'=>'实际价值不能为空|10000',
        //'actual_price.number'=> '实际价值必须为数字|10001',

        'course_model.require'=>'课程模式不能为空|10000',
        'course_model.number'=> '课程模式为数字|10001',

    ];
    public function sceneAdd()
    {
        return $this->only(['cur_name','cur_id','cur_num','cur_value','actual_price','course_model']);
    }
    public function sceneAddone()
    {
        return $this->only(['cur_name','cur_id','cur_num','cur_value','actual_price','course_model']);
    }

    public function sceneEdit()
    {
        return $this->only(['cur_name','cur_id','cur_num','cur_value','actual_price','course_model','meal_cur_id']);
    }
}