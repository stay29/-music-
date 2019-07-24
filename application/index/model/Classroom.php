<?php
/**
 * 教室
 * User: antony
 * Date: 2019/7/16
 */
namespace app\index\model;
use think\Model;
use think\Db;


class Classroom extends BaseModel
{
    protected $pk = 'room_id';
    protected $deleteTime = 'delete_time';
    protected $table = 'erp2_classrooms';
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