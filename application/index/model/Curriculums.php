<?php
namespace app\index\model;
use think\Model;
use think\Db;
use app\index\model\Users;
class Curriculums extends Model
{	 
	protected $table = 'erp2_curriculums';
    protected $pk = 'manager';

	public function profile()
    {
          return $this->hasOne('Users','uid');
    }    	

	public  static  function getall($limit){

		$list = Curriculums::paginate($limit)->each(function($item, $key){
		            if($item['tmethods']=='1'){
						$item['tmethods']='1对1';
					}elseif ($item['tmethods']=="2") {
						$item['tmethods']='1对多';
					}
					if($item['state']=='1'){
						$item['state']='上架';
					}elseif ($item['state']=="2") {
						$item['state']='下架';
					}
					if($item['popular']=='1'){
						$item['popular']='是';
					}elseif ($item['popular']=="2") {
						$item['popular']='不是';
					}
					if($item['conversion']=='1'){
						$item['conversion']='是';
					}elseif ($item['conversion']=="2"){
						$item['conversion']='不是';
					}
					$item['manager'] = $item->profile->account;
				});
		return $list;
	}

	public static function addcurrl($data){
	 	$res = Curriculums::create($data);
		return $res;
	}

	public static function delcurrl($data){
		$res =  Curriculums::where($data)->delete();
		return  $res;
	}
	public static function editcurrm($curid,$data){
		
	 $res = Curriculums::where($curid)->update($data);
		return $res;
	}
	public static function getcurrmone($curid){
		$info = Curriculums::where($curid)->find();
		return $info;
	}
}