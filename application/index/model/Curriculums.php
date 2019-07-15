<?php
namespace app\index\model;
use think\Model;
use think\Db;
use app\index\model\Users;
class Curriculums extends Model
{	 
	protected $table = 'erp2_curriculums';
    protected $pk = 'manager';
    protected $autoWriteTimestamp = true;
    protected $insert = ['status'=>1,'popular'=>2];
    
	public function profile()
    {
          return $this->hasOne('Users','uid');
    }
    //添加课程
    public static function addcurrl($data)
    {
        $res = Curriculums::create($data);
        return $res;
    }

	public  static  function getall($limit)
    {
		$list = Curriculums::field('cur_name,subject,tmethods,ctime,state,status')->paginate($limit);
        return $list;
	}

	public static function delcurrl($data)
    {
		$res =  Curriculums::where($data)->delete();
		return  $res;
	}
	public static function editcurrm($curid,$data)
    {
	 $res = Curriculums::where('cur_id',$curid)->update($data);
	 return $res;
	}
	public static function getcurrmone($curid)
    {
		$info = Curriculums::where($curid)->find();
		$info['manager'] = $info->profile->account;
		return $info;
	}
}