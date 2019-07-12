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
    protected $insert = ['login_time' => 2,'status'=>1,'organization'=>0,'manager'=>1 ,'creator'=>0];
    public function returnUser()
    {
        return $this->belongsTo('Curriculums','uid');
    }
    public  static function  addusers($data)
    {
        //print_r($data);
        $res = Users::create($data, true);
        $uid = $res->uid;
        Users::where(['uid'=>$uid])->update(['nickname'=>'校长'.$uid,'']);

        return $uid;
    }
}