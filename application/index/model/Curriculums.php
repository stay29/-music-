<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Curriculums extends Model
{	
    public $db;
    function __construct(){
        $this->db=Db::name('curriculums');
    }

	//添加课程接口
	public function addc($data){
		$Curriculums->save($data);
		return $Curriculums->id;
	}	

	//课程列表
	public function get_curriculums(){
		$list = $this->db->select();
		foreach ($list as $k => $v) {
			
		}		
		return $list;
	
		
	}

}