<?php
namespace app\validate;
use think\Validate;
class Curriculums extends Validate
{		  
		  protected  $rule = [
              'cur_name'=>'require|max:12',
              'subject'  => 'require|number',
              'tmethods' => 'require|number',
              'ctime' =>    'require|number',
              'describe' => 'max:500',
              'remarks' =>  'max:500',
              'state' =>    'require|number',
              'conversion' => 'require|number',
              'popular' => 'require|number',
              'create_time' => 'require|number',
              'manager' =>  'number',
		  ];

    protected $message = [
        'cur_name.require'=>'课程名称不能为空|10000',
        'cur_name.max'=>    '名称最多不能超过12个字符|10000',

        'subject.require'=>'科目不能选择空|10000',
        'subject.number'=> '科目必须为数字|10001',

        'tmethods.require'=>'授课方式不能选择空|10000',
        'tmethods.number'=> '授课方式必须为数字|10001',

        'ctime.require'=>'授课时长不能选择空|10000',
        'ctime.number'=> '授课时长必须为数字|10001',

        'describe.max'=>'课程描述不能超过500字|10000',
        'remarks.max'=> '备注不能超过500字|10001',

        'state.require'=>'能不能上架不能为空|10000',
        'state.number'=> '能不能上架必须为数字|10001',

        'conversion.require'=>'通用课不能为空|10000',
        'conversion.number'=> '通用课必须为数字|10001',

        'popular.require'=>'是否热门不能为空|10000',
        'popular.number'=> '是否热门必须为数字|10001',

        'create_time.require'=>'创建时间不能为空|10000',
        'create_time.number'=> '创建时间必须为数字|10001',

        'manager.number'=> '创建人必须为数字|10001',
    ];


    protected $scene = [
        'add' =>  ['cur_name','subject','tmethods','ctime','describe','state','conversion','popular'],
        'edit' => ['cur_id','cur_name','subject','tmethods','ctime','describe','state','conversion','popular']
    ];


}
?>