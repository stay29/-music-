<?php
namespace app\common\model;
use think\Model;
class Classes extends Model
{   
    protected $table = 'erp2_classes';
    protected $pk='class_id';
    // protected $pk = 'uid';
      /**
     * 支付方式
     * @return \think\model\relation\Hasone
     */
    public function details()
    {   //belongsToMany('关联模型','中间表','外键','关联键');
        return $this->belongsToMany('students','class_student_relations','stu_id','class_id');
        // return $this->belongsTo(Csres::class,'class_id','class_id');
    }
   
}
