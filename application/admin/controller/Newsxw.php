<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
class Newsxw  extends Comm
{
    public function index(){
    $res = Db::name('new')->select();	
    $this->assign('list',$res);
    return view('newlist');
    }
    public function newlist(){
     
      return view();
    }
    public function addnew(){

    	return view();
    }
    public function addon(){
    $data = input('post.');
    $data['time'] = time();
    $res = Db::name('new')->data($data)->insert();
    if($res){
            echo "1";
    }else{
            echo "2";
    }         
    }
    public function editnew(){
    $data = request()->param();
    $res = Db::name('new')->where($data)->find();
    //$res['contemt'] = html_entity_decode($res['contemt']);
    //print_r($res);
    $this->assign('res',$res);
    return view();
    }
    public function editon(){
    $mup['nid'] = input('post.nid');
    $data = input('post.');
    $res = Db::name('new')->where($mup)->data($data)->update();
    if($res){
            echo "1";
    }else{
            echo "2";
    } 
    }
    public function delnews(){
    $data = input('post.');
    $res = Db::name('new')->where($data)->delete();
    if($res){
            echo "1";
    }else{
            echo "2";
    } 
    }
}