<?php
namespace app\admin\controller; 

class Index extends AdminBase
{

    public function index()
    {
        return $this->fetch();
    }

    public function left(){
        if(session('admin.id') == 1){
            $this->admin_access_id = $this->all_accesses(2);
        }
    	$accesses = db("admin_accesses")
            ->whereIn('id',$this->admin_access_id)
            ->where(['status'=>1,'show'=>1])
            ->field('aname,aurl,id,pid') 
            ->select();
    	$accesses = $this->make_tree($accesses);
    	$this->assign('accesses',$accesses);
    	return $this->fetch();
    }
    public function right(){
    	$admin = session('admin');
    	$this->assign('ad_account',$admin['ad_account']);
    	$this->assign('ad_login_time',$admin['ad_login_time']);
    	$this->assign('ad_create_time',$admin['ad_create_time']);
        $rnames = db("admin_roles")->join('erp2_admin_role_relations arm','arm.role_id = erp2_admin_roles.id')->where(['admin_id'=>$admin['id']])->column('rname');
        if($admin['id'] == 1){
            $rnames = '超级管理员';
        }else if($rnames){
            $rnames = implode('/',$rnames);
        }else{
            $rnames = '暂无身份';
        }
        $this->assign('rnames',$rnames);
    	return $this->fetch();
    }

     public function logout(){
        if(session('?admin')){
              session_destroy();
              $this->redirect(url('admin/login/index'),302);
        }
    }

}
