<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\File;
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

 public function uploadssss()
{
    // 获取上传文件
    $file = request() -> file('myfile');       
    // 验证图片,并移动图片到框架目录下。
    $info = $file->rule('uniqid')-> move('./updates/');
    if($info){
        // $info->getExtension();         // 文件扩展名
        $mes = $info->getSaveName();      // 文件名
        echo '/public/updates/'.$mes;
    }else{
        // 文件上传失败后的错误信息
        $mes = $file->getError();
        echo '{"mes":"'.$mes.'"}';
    }
}
}