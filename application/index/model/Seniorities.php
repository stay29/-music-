<?php
/**
 * 教师资历
 * User: antony
 * Date: 2019/7/16
 * Time: 14:56
 */

namespace app\index\model;


class Seniorities extends BaseModel
{
    protected $table = 'erp2_seniorities';
    protected $field = true;
    protected $autoWriteTimestamp = true;
    protected $auto = ['manager'];//操作人id，对应users表主键
    protected $update = [];
    protected function setManagerAttr(){
        if(!empty(session(md5(MA.'user')))){
            return session(md5(MA.'user'))['id'];
        }else{
            return 0;
        }
    }

    //关联教师表
    protected function teacher(){
        return $this->hasOne('Teacher','se_id','seniority_id');
    }

}