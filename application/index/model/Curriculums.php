<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Curriculums extends Model
{	
	//课程列表
	public function get_curriculums(){
		$Curriculums  = new Curriculums;
		$list = $Curriculums->select();
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
	public function addcurrl($data){
		$Curriculums  = new Curriculums;
		$Curriculums->save($data);
		return $Curriculums->id;
	}
	public function delcurrl($data){
		$Curriculums  = new Curriculums;
		$res =  $Curriculums->where($data)->delete();
		return  $res;
	}
	public function editcurrm($curid,$data){
		$Curriculums  = new Curriculums;
		$Curriculums->where($curid)->save($data);
		return $Curriculums->id;
	}
	public function getcurrmone($curid){
		$Curriculums  = new Curriculums;
		$info = $Curriculums->where($curid)->find();
		return $info;
	}

}