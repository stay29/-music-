<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Curriculums extends Model
{	
	
	public function addc($data){
		$user  = new Erp2Curriculums;
		$user->save($data);
		return $user->id;
	}		
}