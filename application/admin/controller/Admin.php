<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
class Admin  extends Comm
{
   public function adminlist(){
    $mup  = input('post.');
    $mup2 =  array_filter($mup);
    $res  =  Db::table('erp2_admins')->where($mup2)->select();
    foreach ($res as $k => &$v) {
        $mup1['rid'] =  $v['rid'];
        $v['rname'] = Db::table('erp2_admin_roles')->where($mup1)->find();
        $v['time'] = date('Y-m-d',$v['create_time']);
    }
    $rolelist = Db::table('erp2_admin_roles')->select();
    $this->assign('rolelist',$rolelist);
    $this->assign('list',$res);
    if (request()->isAjax()) {
            return view("auth/adminlisttable");//无刷新搜索
    } else {
            return view();
    }
   }
   public function addadmin(){
     $data = request()->param();
     $rolelist =   Db::table('erp2_admin_roles')->select();
     if (empty($data)) {
             $adminfind = Db::table('erp2_admins')->find();
              $this->assign('list',$rolelist); 
              return view();
     }else{
             $adminfind = Db::table('erp2_admins')->where($data)->find();
            $this->assign('afid',$adminfind);
            $this->assign('list',$rolelist); 
            return view('editadmin');
     }
   } 
   public function addon(){
    $data = input('post.');
    $admin_id = input('post.admin_id');
    $data['create_time'] = time();
    $data['status'] = 1;
    if ($admin_id) {
        $aid['admin_id'] = $admin_id;
        $res =  Db::table('erp2_admins')->where($aid)->update($data);
    }else{
    unset($data['admin_id']);
    $res = Db::table('erp2_admins')
    ->data($data)
    ->insert(); 
    }
    if($res){
        echo "1";
    }else{
        echo "2";
    }
   }

    public function admindel(){
        $data = input('post.'); 
          $res = Db::name('erp2_admins')
                ->where($data)
                ->delete(); 
            if($res){
                echo "1";
            }else{
                echo "2";
            }
    }
}