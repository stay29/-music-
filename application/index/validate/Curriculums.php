<?php
namespace app\index\validate;
use think\Validate;
class Curriculums extends Validate
{		  
		  protected  $rule = [
              'cur_name'  =>  'require|max:12',
              'subject'  =>   'require|number',
              'tmethods' =>   'require|number',
              'ctime' =>      'require|number|between:15,240',
              'tqualific' =>  'require',
              'state' =>      'number',
              'conversion' => 'number',
              'describe' =>   'max:500',
              'remarks' =>    'max:500',
//              'listimg' =>    'require',
//              'infoimg' =>    'require',
		  ];
    protected $message = [
        'cur_name.require'=>'课程名称不能为空|10000',
        'cur_name.max'=>    '名称最多不能超过12个字符|10001',

        'subject.require'=>'科目不能选择空|10000',
        'subject.number'=> '科目必须为数字|10001',

        'tmethods.require'=>'授课方式不能选择空|10000',
        'tmethods.number'=> '授课方式必须为数字|10001',

        'ctime.require'=>'授课时长不能选择空|10000',
        'ctime.number'=> '授课时长必须为数字|10001',
        'ctime.between'=> '授课时长必须在15-240分钟|10001',

        'tqualific.require'=> '教师资历不能选择空|10000',

        // 'state.require'=>'能不能上架不能为空|10000',
        'state.number'=> '能不能上架必须为数字|10001',

        //'conversion.require'=>'通用课不能为空|10000',
        'conversion.number'=> '通用课必须为数字|10001',

        //'describe.require'=>'课程描述不能为空|10000',
        'describe.max'=>'课程描述不能超过500字|10001',

        //'remarks.require'=>'备注不能为空|10000',
        'remarks.max'=> '备注不能超过500字|10001',

        //'listimg.require'=> '列表图片不能为空|10000',
        //'infoimg.require'=> '详情图片不能为空|10000',
    ];

    protected $scene = [
        'add' =>  ['cur_name','subject','tmethods','ctime','tqualific','state','conversion'
            ,'describe','remarks','listimg','infoimg','manager'],
        'edit' =>['cur_id','cur_name','subject','tmethods','ctime','tqualific','state','conversion','describe','remarks','listimg','infoimg','manager'],
    ];

}
?>