<?php
/**
 * 机构
 * User: antony
 * Date: 2019/7/10
 * Time: 14:56
 */
namespace app\index\model;
class Organization extends BaseModel
{
    protected $table = 'erp2_organizations';
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



}