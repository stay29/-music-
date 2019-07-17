<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Users extends Model
{
	protected $table = 'erp2_users';
    protected $pk = 'uid';
    protected $field = true;
    protected $autoWriteTimestamp = true;
    protected $insert = ['status'=>1];
    public function returnUser()
    {
        return $this->belongsTo('Curriculums','uid');
    }

    public  static function  addusers($data)
    {
        $res = Users::create($data, true);
        $suid = session(md5(MA.'user'))['id'];
        Users::where(['uid'=>$res->uid])
        ->update(['nickname'=>'校长'.$res->uid,'update_time'=>time(),'login_time'=>time()]);
        return $res->uid;
    }
    public static function  get_one_info($data)
    {
        $res = Users::where($data)->find();
        return $res;
    }
    public static function  edit_one_info($uid,$data)
    {
       $res =  Users::where($uid)->update($data);
       return $res;
    }

}