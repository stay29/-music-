<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-23
 * Time: 上午9:28
 */

namespace app\index\validate;


use think\Validate;

class Goods extends Validate
{
    protected $rule = [
        'goods_name' => 'require|max:20',
        'orgid' => 'require',
        'cate_id' => 'require',
        'unit_name' => 'require|max:4',
        'rent_amount_mon' => 'require',
        'rent_amount_day' => 'require',
        'rent_amount_year' => 'require',
        'goods_amount'  => 'require',
        'rent_amount'  => 'require',
        'remarks'  => 'max:500',
    ];

    protected $message = [
        'orgid.require' => '10000|机构ID必填',
        'goods_name.require' => '10000|商品名称必填',
        'goods_name.max' => '10001|商品名称20字符内',
        'cate_id.require'  => '10000|分类id必填',
        'unit_name.require' => '单位名称必填',
        'unit_name.max'     => '单位名称4字符内',
        'rent_type.require' => '租金类型必填',
        'remarks.max'  => '备注500字符内',
        'goods_amount.require' => '商品售价必填',
        'margin_amount.require'  => '押金金额必填',
        'rent_id.require'  => '租金类型必填',
        'rent_amount_day.require'  => '租金金额(日)必填',
        'rent_amount_mon.require'   => '租金金额(月)必填',
        'rent_amount_year.require'  => '租金金额(年)必填',
    ];
}