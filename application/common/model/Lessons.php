<?php
namespace app\common\model;
use think\Model;
class Lessons extends Model
{
    protected $table = 'erp2_purchase_lessons';
    /**
     * 关联学生表
     * @return \think\model\relation\Hasone
     */
    public function student()
    {
        return $this->hasOne('students','stu_id','stu_id');
    }

}
