<?php

namespace app\index\validate;

use think\Validate;

class Students extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'truename|姓名' => 'require|max:16',
        'sex|性别' => 'require|number|between:1,2',
        'cellphone' => 'require|max:11|/^1[3-9]{1}[0-9]{9}$/',
        'email' => 'email',
        'wechat' => 'chsDash',
        'address' => 'max:100',
        'remark' => 'max:500',
        'grade' => 'number:1,9',
        'status' => 'number|between:1,2',
        'school_name' => 'chsAlpha',
        'guardian_name' => 'chsAlpha',
        'guardian_phone' => 'max:11|/^1[3-9]{1}[0-9]{9}$/'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'truename.require' => '姓名必须',
        'truename.max' => '姓名不超过16个字符',
        'sex.require' => '性别必填',
        'sex.number' => '性别必须为数字:1男,2女',
        'sex.between' => '性别必须为１(男)或２(女)',
        'cellphone'  => '手机号非法',
        'email' => '邮箱格式错误',
        'guardian_name' => '监护人姓名错误',
        'wechat' => '微信号非法',
        'address' => '地址最多100个字符',
        'remark' => '备注最多500个字符',
        'grade' => '年级错误',
        'school_name' => '学校名称错误',
        'guardian_phone' => '监护人手机号错误',
        'status' => '状态必须为1:启用, 2:禁用'
    ];

    // add验证场景定义
    public function sceneAdd()
    {
        return $this->only(['truename','sex','cellphone', 'address', 'status', 'remark']);
    }
    // edit验证场景定义
    public function sceneEdit()
    {
        return $this->only(['stu_id','truename','sex', 'remark', 'cellphone',
            'email', 'guardian_name', 'guardian_phone',
            'address', 'grade', 'status', 'wechat', 'school_name']);
    }

}
