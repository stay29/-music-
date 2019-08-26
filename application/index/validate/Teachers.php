<?php

namespace app\index\validate;

use think\Validate;

class Teachers extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        't_id'=>'require',
        't_name'  => 'require|max:8',
        'cellphone'  => 'require|max:11|mobile|unique:Teachers',
        'se_id' => 'require|integer',
        'sex' => 'integer',
        'entry_time' => 'require',
        'birthday' => 'require',
        'id_card' => 'idCard',
        'status' => 'integer',
        'resume' => 'max:500'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        't_id.require'=>'教师ID不得为空|10000',
        't_name.require'=>'教师名称不得为空|10000',
        't_name.max'=>'教师名称长度不得大于8|10001',
        'cellphone.require'=>'请填写手机号|10000',
        'cellphone.max'=>'手机号不得大于11位|10001',
        'cellphone.mobile'=>'手机号格式不正确|10001',
        'cellphone.unique'=>'手机号已存在|20000',
        'id_card.idCard'=>'身份证格式不正确|10001',
        'entry_time.require'=>'请选择入职日期|10000',
        'se_id.require'=>'请选择资历|10000',
        'se_id.integer'=>'资历格式不正确|10001',
        'sex.integer'=>'性别格式不正确|10001',
        'status.integer'=>'状态格式不正确|10001',
        'resume.max' => '简历最多500个字符'
    ];

    protected $scene = [
        'add' => ['t_name','se_id','cellphone','entry_time','id_card','sex','status'],
        'edit' => ['t_id', 'se_id','cellphone','entry_time','id_card','sex','status', 't_name']
    ];
}
