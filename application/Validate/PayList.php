<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 14:19
 */
namespace app\validate;
use think\Validate;
class PayList extends  Validate
{

    protected  $rule = [

        'pay_id_list'  =>     'require|number',
        'cur_id'  =>     'require|number',
        'cur_name'  =>   'require',
        'pless_id'  =>   'require|number',
        'p_id'  =>       'require|number',
        'bay_paich'  =>  'require|number',
        'remake'  =>     'require|max:200',
        'orgid'  =>      'require|number',
        'manager'  =>    'require|number',

    ];

    protected $message = [
        'pay_id_list.require'=>      '薪酬id不能为空|10000',
        'pay_id_list.number'=>       '薪酬id必须为数字|10001',

        'cur_id.require'=>      '课程id不能为空|10000',
        'cur_id.number'=>       '课程id必须为数字|10001',

        'cur_name.require'=>    '课程名称不能为空|10000',

        'pless_id.require'=>    '计算不能为空|10000',
        'pless_id.number'=>     '计算必须为数字|10001',

        'p_id.require'=>        '计算方式不能为空|10000',
        'p_id.number'=>         '计算方式必须为数字|10001',

        'bay_paich.require'=>   '结算金额不能为空|10000',
        'bay_paich.number'=>    '结算金额必须为数字|10001',

        'remake.require'=>      '备注不能为空|10000',
        'remake.max'=>          '备注必须不能超过200个字|10001',

            'orgid.require'=>   '机构id不能为空|10000',
            'orgid.number'=>    '机构id必须为数字|10001',

            'manager.require'=>   '用户id不能为空|10000',
            'manager.number'=>    '用户id必须为数字|10001',
    ];


    //添加场景
    public function sceneAdd()
    {
        return $this->only(
            [
                'cur_id',
                'cur_name',
                'pless_id',
                'p_id',
                'remake',
                'bay_paich',
                'orgid',
                'manager',
            ]);
    }
    //删除场景
    public  function  sceneDel()
    {
        return $this->only(
            [
                'pay_id_list',
                'orgid',
                'manager',
            ]);
    }


}