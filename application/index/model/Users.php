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
    protected $insert = ['nickname' => '校长'.,'login_time' => 1,'update_time'=>'time' ,'status'=>1,'organization'=>0,'manager'=>1 ,'creator'=>0];

    public function returnUser()
    {
        return $this->belongsTo('Curriculums','uid');
    }
    public  static function  addusers($data)
    {
        //print_r($data);
        $res = Users::create($data, true);
        $uid = $res->uid;

        return $uid;
    }
}