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
        $data['orgid'] = session(md5(MA.'user'))['orgid'];
        $res = Curriculums::create($data);
        return $res;
    }

	public  static  function getall($limit,$where)
    {
            //$where['orgid'] = session(md5(MA.'user'))['orgid'];
            $list = Curriculums::where($where)
            ->paginate($limit)->each(function($item, $key){
            $where1['sid'] = $item['subject'];
            $item['subject'] = db('subjects')->field('sid,sname,pid')->where($where1)->find();
            $tqualific = explode('/',$item['tqualific']);
            $item['ordinary_tqualific'] = $tqualific[0];
            $item['senior_tqualific'] = $tqualific[1];
        });
        return $list;
	}
	public static function delcurrl($data)
    {
		$res =  Curriculums::where($data)->delete();
		return  $res;
	}

	public static function editcurrm($curid,$data)
    {
     $data['orgid'] = session(md5(MA.'user'))['orgid'];
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