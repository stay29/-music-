<?php
namespace app\index\controller;
/*
管理员模块
 */
class New extends Comm
{	
	//新闻列表
    public function index()
    {
       $newlist = Db::table('admin')->select();
       $this->assign('list',$newlist);
       return view();
    }
    //添加新闻 判断id是不是为空进行更新和添加
    public function addnew(){
    	$data = input('post.');
    	//成功返回1
    	if ($data['id']==null) {
    	$res = Db::table('admin')
    	->data($data)
    	->insert();
    	}else{
    	$mup['id'] = $data['id'];
    	$res = Db::table('admin')
    	->where($mup)
    	->update($data);
    	}
    	if ($res) {
    		$this->success('成功',url('New/index'));
    	}else{
    		$this->error('失败',,url('New/index'));
    	}
    }
    //删除新闻
    public function delnew(){
    	$mup = input('post.id');
    	$res = Db::table('admin')
    	->where($mup)
    	->delete();
    	if ($res) {
    		$this->success('成功',url('New/index'));
    	}else{
    		$this->error('失败',,url('New/index'));
    	}
    }
    //屏蔽进去回收站
    public function  Shieldnew(){
    	$mup = input('post.id');
    	$data['shield'] = 2;
    	$res = Db::table('new')->where($mup)->update($data);
    	if ($res) {
    		$this->success('成功',url('New/index'));
    	}else{
    		$this->error('失败',,url('New/index'));
    	}
    }

    

}
