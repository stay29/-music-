<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\facade\Session;
class Index  extends Comm
{
    public function index()
    {   
        $mup['admin_id'] = Session::get('aid');
        //print_r($mup);exit();
        $res = Db::table('erp2_admins')->where($mup)->find();
        $mup1['rid'] = $res['rid'];
        $roleres = Db::table('erp2_admin_roles')->where($mup1)->find();
        $authlist = explode('.', trim($roleres['rpid'],'.'));
        $list = array();
        foreach ($authlist as $k => &$v) {
            $mup2['aid'] = $v;
            $mup2['pid'] = 0;
            $a = Db::table('erp2_admin_auths')->where($mup2)->find();
            if($a){
                $list[]=$a;
            }
        }
        foreach ($list as $k1 => &$v1) {
            $mup3['pid'] = $v1['aid'];
            $v1['list'] = Db::table('erp2_admin_auths')->where($mup3)->select();
         }
        $this->assign('alist',$list);
    	return view();
    }
    public function auth(){
        $mup = input('post.');
        $authlist = Db::table('erp2_admin_auths')->where($mup)->select();
          foreach ($authlist as $k => &$v) {
            if($v['pid']==0){
                $v['pidname'] = '顶级分类';
            }else{
                $pidname = Db::table('erp2_admin_auths')->field('aname')->find();
                $v['pidname'] =  $pidname['aname'];
            }
        }
            $this->assign('list',$authlist);
            $this->assign('clist',count($authlist));
            if (request()->isAjax()) {
                return view("auth/authtable");//无刷新搜索
            } else {
                return view();
            }
    }


    public function addauth(){
        $authlist = Db::table('erp2_admin_auths')->where('pid',0)->select();
        $this->assign('list',$authlist);
        return view();
    }

    public function addon(){
        $data = input('post.');
        if ($data['pid']==0) {
            unset($data['url']);
        }
        $data['state'] = 1;
        $res = Db::name('erp2_admin_auths')
                ->data($data)
                ->insert();
        if($res){
            echo "1";
        }else{
            echo "2";
        }
    }
    public function delauth(){
        $data = input('post.');
        //print_r($data);exit();
        $res = Db::name('erp2_admin_auths')
                ->where($data)
                ->delete();
        if($res){
            echo "1";
        }else{
            echo "2";
        }       
    }
    public function rolelist(){
    $rolelist =   Db::table('erp2_admin_roles')->select();
    $this->assign('list',$rolelist);
    return view();     
    }
    public function addrole(){
       $authlist =  Db::table('erp2_admin_auths')->where('pid',0)->select();
       foreach ($authlist as $k => &$v) {
        $mup['pid'] = $v['aid'];
        $v['list'] =     Db::table('erp2_admin_auths')->where($mup)->select();
       }
       $this->assign('list',$authlist);
       return view();     
    }
    public function addroleon(){
    $data = input('post.');
    $data['state'] = 1;
    $res = Db::name('erp2_admin_roles')
       ->data($data)
       ->insert(); 
       if($res){
            echo "1";
        }else{
            echo "2";
        }
    }
    public function roledel(){
       $data = input('post.');
       //print_r($data);exit();
       $res = Db::name('erp2_admin_roles')
                ->where($data)
                ->delete();
        if($res){
            echo "1";
        }else{
            echo "2";
        }
    }

}