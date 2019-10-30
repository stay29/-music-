<?php
namespace app\common\model;
use think\Model;
class Purchase_Lessons extends Model
{
    protected $table = 'erp2_purchase_lessons_record';

     /*转换购买时间戳*/
     public function getBuyTimeAttr($value, $data)
    {
   
        return date('Y-m-d H:i',$value);
    }

    /**
     * 关联学生表
     * @return \think\model\relation\Hasone
     */
    public function student()
    {
        return $this->hasOne(Students::class,'stu_id','stu_id');
    }

    /**
     * 操作者
     * @return \think\model\relation\Hasone
     */
        public function users()
    {
        return $this->hasOne(User::class,'uid','manager');
    }
    /**
     * 支付方式
     * @return \think\model\relation\Hasone
     */
    public function pay()
    {
        return $this->hasOne(Payments::class,'pay_id','pay_id');
    }

}
