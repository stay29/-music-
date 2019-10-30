<?php
namespace app\common\model;
use think\Model;
class Expend extends Model
{
    protected $table = 'erp2_expend_log';
    /*
     *录入时间
     */
    public  function getMarkTimeAttr($value,$data){
      return date('Y-m-d H:i',$value);
    }
    /*
     *录入时间
     */
    public  function getPayTimeAttr($value,$data){
      return date('Y-m-d',$value);
    }
    /**
     * 关联类型表
     * @return \think\model\relation\Hasone
     */
    public function type()
    {
        return $this->hasOne('expend_type','id','type_id');
    }
}
