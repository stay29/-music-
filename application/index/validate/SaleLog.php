<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-30
 * Time: 下午7:02
 */

namespace app\index\validate;

/*
 * 销售记录验证
 */

use think\Validate;

class SaleLog extends Validate
{
    protected $rule = [
        'sale_id' => 'require|number',
        'sale_num' =>  'require|number',
        'single_price'  => 'require|number',
        'sum_payable'  => 'require|number',
        'pay_amount'    => 'require|number',
        'sale_time'     => 'require|number',
        'pay_id'        => 'require|number',
    ];

    protected $message = [
        'sale_id.require'   => '销售员id不能为空|10000',
        'sale_id.number'   => '销售员id必须为数字|10001',
        'sale_num.require'  => '销售数量不能为空|10001',
        'sale_num.number'   => '销售数量必须为数字|10001',
        'single_price.require'  => '销售单价必填|10000',
        'single_price.number'   => '销售单价必须为数字|10001',
        'pay_amount.number' => '实付金额必须为数字|10000',
        'pay_amount.require'    => '实付金额必填|10000',
        'sale_time.require' => '销售时间必填|10000',
        'sale_time.number'  => '销售时间必须为数字|100001',
        'pay_id.require'    => '支付方式必填|10000',
        'pay_id.number' => '支付方式必须为数字|10001',

    ];
}