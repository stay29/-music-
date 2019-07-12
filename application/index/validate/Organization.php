<?php
/**
 * 教师
 * User: antoyn
 * Date: 2019/7/11
 * Time: 10:23
 */

namespace app\index\validate;


use think\Validate;

class Organization extends Validate
{
    protected $rule = [
        'or_id'=>'require',
        'or_name'  => 'require|max:8',
        'se_id' => 'require|integer',
        'sex' => 'integer',
        'cellphone'  => 'require|max:11|mobile|unique:Teachers',
        'entry_time' => 'require',
        'birthday' => 'date',
        'id_card' => 'idCard',
        'status' => 'integer',
    ];


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
        'entry_time.date'=>'入职日期错误|10001',
        'birthday.date'=>'生日日期错误|10001',
        'se_id.require'=>'请选择资历|10000',
        'se_id.integer'=>'资历格式不正确|10001',
        'sex.integer'=>'性别格式不正确|10001',
        'status.integer'=>'状态格式不正确|10001',
    ];

    protected $scene = [
        'add' => ['t_name','se_id','cellphone','entry_time','id_card','sex','status','birthday'],
        'edit' => ['t_id','t_name','se_id','cellphone'=>'require|max:11|mobile|unique:Teachers,t_id','entry_time','id_card','sex','status','birthday']
    ];



}