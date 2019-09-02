<?php
/**
 * 机构
 * User: antoyn
 * Date: 2019/7/11
 * Time: 10:23
 */
namespace app\index\validate;
use think\Validate;
class Organization extends Validate
{
    protected $rule = [
        'or_id'=>       'require',
        'or_name'  =>   'require|max:20|unique:Organizations',
        'telephone'  => 'require|max:11|mobile',
        'wechat'  =>    'require|min:5|max:20',
        'describe' =>   'require|max:500',
        'remarks' =>    'max:500',
        'address' =>    'require|max:128',
    ];
    protected $message = [
        'or_id.require'=>       '机构ID不得为空|10000',
        'or_name.require'=>     '机构名称不得为空|10000',
        'or_name.max'=>         '机构名称长度不得大于20位|10001',
        'or_name.unique'=>      '机构已存在|20000',
        'telephone.require'=>   '请填写手机号|10000',
        'telephone.max'=>'手机号不得大于11位|10001',
        'telephone.mobile'=>'手机号格式不正确|10001',
//      'telephone.unique'=>'手机号已存在|20000',
        'wechat.require'=>'微信不得为空|10000',
        'wechat.max'=>'微信号长度超过20字符|10001' ,
        'wechat.min' => '微信号长度小于5字符|10001',
        'describe.require'=>'简介不得为空|10000',
        'describe.max'=>'简介字数不得大于500|10001',
        'remarks.max'=>'备注字数不得大于500|10001',
        'address.require'=>'地址不得为空|10000',
        'address.max'=>'地址字数不得大于128|10001',
    ];

    // add验证场景定义
    public function sceneAdd()
    {
        return $this->only(['or_name','wechat','telephone','describe','remarks', 'address']);
    }


}