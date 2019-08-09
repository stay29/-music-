<?php
/**
 * 教室
 * User: antony
 * Date: 2019/7/16
 */

namespace app\index\validate;


use think\Validate;

class Classroom extends Validate
{
    protected $rule = [
        'room_id'=>'require',
        'room_name'=>'require|max:8',
        'room_count'=>'require|between:1,9999',
        'status'=>'require|in:1,2',
    ];


    protected $message = [
        'room_id.require'=>'教室id不得为空|10000',
        'room_name.require'=>'教室名称不得为空|10000',
        'room_name.max'=>'教室名称不得长于8|10001',
        'room_name.unique'=>'教室名称不能重复|20000',
        'room_count.require'=>'教室容量不得为空|10000',
        'room_count.between'=>'教室容量格式不正确|10001',
        'status.require'=>'教室状态不得为空|10000',
        'status.in'=>'教室状态必须是1或2|10001',
    ];

    // add验证场景定义
    public function sceneAdd()
    {
        return $this->only(['room_name','room_count','status']);
    }
    // edit验证场景定义
    public function sceneEdit()
    {
        return $this->only(['room_id','room_name','room_count','status']);
    }

}