<?php

namespace app\index\model;

use think\Model;

class Students extends Model
{
    protected $pk = 'stu_id';

    // auto write create_time, update_time
    protected $autoWriteTimestamp = true;

    //
    protected $insert = ['status' => 1];

    protected $type = [
        'stu_id'  =>  'integer',
        'truename' => 'truename',
        'status'    =>  'integer',
        'birthday'  => 'timestamp:Y/m/d',
        'cellphone'  => 'string',
        'sex'   => 'integer',
    ];

    protected function setManagerAttr(){
        if(!empty(session(md5(MA.'user')))){
            return session(md5(MA.'user'))['id'];
        }else{
            return 0;
        }
    }
}
