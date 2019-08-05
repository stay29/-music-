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
    protected $pk = 't_id';
    protected $table = 'erp2_teachers';
    protected $field = true;
    protected $autoWriteTimestamp = true;
    protected $auto = ['manager','entry_time'];//操作人id，对应users表主键
    protected $append = ['seniority_name'];
    protected $update = [];

    protected function setManagerAttr(){
        if(!empty(session(md5(MA.'user')))){
            return session(md5(MA.'user'))['id'];
        }else{
            return 0;
        }
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

    //资历
     function getSeniorityNameAttr($value, $data){
        return Seniorities::where('seniority_id', $data['se_id'])->value('seniority_name');
    }

    //关联资历表
    protected function seniority(){
        return $this->hasOne('Seniorities','seniority_id','se_id');
    }
}