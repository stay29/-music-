<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/13
 * Time: 9:38
 */
namespace app\index\validate;
use think\Validate;
class Classes extends Validate
{
    protected $rule = [
        'class_name'=>'require|max:20',
        'class_count'=>'require',
        'headmaster'=>'require',
        'remarks'=>'max:500',
        'orgid'=>'require',
    ];


    protected $message = [
        'class_name.require'=>'班级名称不得为空|10000',
        'class_name.max'=>'班级名称不得超过20个字|10000',
        'class_count.require'=>'班级人数不得为空|10000',
        'headmaster.require'=>'班主任id不得为空|10000',
        'remarks.max'=>'备注不得超过500个字|10000',
        'orgid.require'=>'机构id不得为空|10000',
    ];


    // add验证场景定义
    public function sceneAdd()
    {
        return $this->only(['class_name','class_count','headmaster','remarks','orgid']);
    }
    // edit验证场景定义
    public function sceneEdit()
    {
        return $this->only(['room_id','room_name','room_count','status']);
    }

}
