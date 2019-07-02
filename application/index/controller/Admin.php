<?php
namespace app\index\controller;
/*
管理员模块
 */
class Admin extends Comm
{	
	//管理员列表
    public function index()
    {
       $adminlist = Db::table('admin')->select();
       $this->assign('list',$adminlist);
       return view();
    }
    //

}
