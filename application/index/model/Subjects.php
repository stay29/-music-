<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Subjects extends Model
{	     
	protected $table = 'erp2_subjects';
    protected $pk = 'sid';

    public static function getall(){
    	$where['pid'] = 0;
    	$res = Subjects::where($where)->all()->each(function($item, $key){
    		$where1['pid'] = $item['sid'];
    		$item['pids'] = Subjects::where($where1)->find();
    	});
    	return $res;
    }

}