<?php
namespace app\common\model;
use think\Model;
class TeachSchedules extends Model
{
    protected $table = 'erp2_teach_schedules';
     /*
     *类型转换
     */
    public  function getTypeAttr($value,$data){
      if($value == 1){
        return '一对一';
      }else{
        return '一对多';
      }
    }
}
