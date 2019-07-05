<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\File;
class Index extends Controller
{
    public function index()
    {
    	return view();
    }
    public function article()
    {
        $res = Db::name('new')->find();
        $this->assign('res',$res);
        return view();
    }
    public function cyff()
    {
        return view();
    }
    public function gywm()
    {
        return view();
    }
    public function jdxmbm()
    {
        return view();
    }
    public function jdxmm()
    {
        return view();
    }
    public function lists()
    {   
        $id =  request()->param('id');
        $res= Db::name('new')->select();
        $this->assign('res',$res);
        return view('list');
    }
    public function sfbz()
    {
        return view();
    } 
    public function jdxmwc()
    {
        return view();
    }    
    public function jdxmgt()
    {
        return view();
    } 
    public function jdxmlh()
    {
        return view();
    }
    public function lxwm()
    {
        return view();
    }     
}
