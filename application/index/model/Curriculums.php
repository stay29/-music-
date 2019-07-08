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
		foreach ($list as $k => &$v) {
			if($v['tmethods']=='1'){
				$v['tmethods']='1对1';
			}elseif ($v['tmethods']=="2") {
				$v['tmethods']='1对多';
			}
			if($v['state']=='1'){
				$v['state']='上架';
			}elseif ($v['state']=="2") {
				$v['state']='下架';
			}
			if($v['popular']=='1'){
				$v['popular']='是';
			}elseif ($v['popular']=="2") {
				$v['popular']='不是';
			}
			if($v['conversion']=='1'){
				$v['conversion']='是';
			}elseif ($v['conversion']=="2") {
				$v['conversion']='不是';
			}
			$mup['sid']	= $v['subject'];
			$v['subject'] = db('subjects')->where($mup)->find();
		 }		
		    return $list;
	}
}