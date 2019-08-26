<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-23
 * Time: 上午9:22
 */

namespace app\index\model;


use think\Model;

class Goods extends Model
{
    protected $table = 'erp2_goods_detail';
    protected $pk = 'goods_id';
    protected $autoWriteTimestamp = true;

}