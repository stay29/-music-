<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Users extends Model
{	     
	protected $table = 'erp2_users';
    protected $pk = 'uid';
 
    public function returnUser()
    {
        return $this->belongsTo('Curriculums','uid');
    }


    public  static function  addusers($data)
    {
        $res = Users::cache($data);
        return $res;
    }

}