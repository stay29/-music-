<?php
namespace app\common\model;
use think\Model;
class Balance extends Model
{
    protected $table = 'erp2_stu_balance';
    /**
     * 关联学生表
     * @return \think\model\relation\Hasone
     */
    public function student()
    {
        return $this->hasOne(Students::class,'stu_id','stu_id');
    }
}
