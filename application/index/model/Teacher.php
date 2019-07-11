<?php
/**
 * 教师
 * User: antony
 * Date: 2019/7/10
 * Time: 14:56
 */

namespace app\index\model;


class Teacher extends BaseModel
{
    protected $table = 'erp2_teachers';
    protected $autoWriteTimestamp = true;
    protected $auto = ['manager','entry_time'];//操作人id，对应users表主键

    protected $insert = ['status'=>2];
    protected $update = [];
    protected function setManagerAttr(){
        return 1;
    }
    //入职日期
    protected function setEntryTimeAttr($data){
        $is_date=strtotime($data)?strtotime($data):false;
        if($is_date===false){//非法格式
            return $data;
        }else{//合法格式
            return strtotime($data);
        }
    }
    //生日
    protected function setBirthdayAttr($data){
        $is_date=strtotime($data)?strtotime($data):false;
        if($is_date===false){//非法格式
            return $data;
        }else{//合法格式
            return strtotime($data);
        }
    }


}