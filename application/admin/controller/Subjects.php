<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\facade\Session;
class Subjects  extends Comm
{
   public function index(){
   	$page = input('page') ? input('page') : 1;
   	$count = Db::table('erp2_admin_auths')->count();
   	$co = ceil($count/2);
   	if($page==0){
   		$page =1;
   	}
   	if ($page>$co) {
   		$page=$co;
   	}
   	$res = Db::table('erp2_admin_auths')->limit(2)->page($page)->select();
   	$spage = $page-1;
   	$xpage = $page+1;
   	$this->assign('list', $res);
  	$this->assign('page', $page);
   	return view();
   }
}