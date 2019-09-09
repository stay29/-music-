<?php


namespace app\index\model;


use think\Model;

class StuBalance extends Model
{
    protected $pk = 'b_id';
    // auto write create_time, update_time
    protected $autoWriteTimestamp = true;
    protected $table = 'erp2_stu_balance';
}