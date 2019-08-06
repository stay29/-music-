<?php
namespace app\admin\controller; 

class Adminer extends AdminBase
{

    public function initialize(){
        //获取角色
        $this->assign('roles',$this->role());
    }
    private function role(){
        return db('admin_roles')->field('rname,id')->select();
    }
    public function index()
    { 
    	$this->assign('title','管理员列表');
		$this->assign('add',url('add'));

    	$admins = db('admins')->paginate(20)->each(function($v,$k){
            if($v['ad_status'] == 1){
                $v['ad_status_text'] = '正常';
            }elseif($v['ad_status'] == 2){
                $v['ad_status_text'] = '禁用';
            }
           $ad_manager = db('admins')->where(['id'=>$v['ad_manager']])->value('ad_account');
               if($ad_manager){
                  $v['ad_manager'] = '超级管理员';
            }

            return $v;
        });
 
    	$this->assign('admins_list',$admins);
        return $this->fetch();
    }

    public function add(){
    	if(input('post.')){
    		$data = input('post.');
    		if($data['ad_password'] != $data['ad_password_confirm']){
    			$this->return_data(0,'密码不一致');
    		}
    		unset($data['ad_password_confirm']);
    		if(db("admins")->where(['ad_account'=>$data['ad_account']])->count()){
    			$this->return_data(0,'该管理员已存在');
    		}
    		$data['ad_secret'] = mt_rand(10000,99999);
    		$data['ad_password'] = sha1($data['ad_password'].$data['ad_secret']);
    		$data['ad_create_time'] = $data['ad_update_time'] = time();
    		$data['ad_manager'] = session('admin.id');
            if(isset($data['role_ids'])){
                $data_tmp['role_ids'] = $data['role_ids'];
                unset($data['role_ids']);
            }else{
                $data_tmp['role_ids'] = [];
            }
    		$res = db('admins')->data($data)->insert();
    		if($res){
                //插入角色
                if($data_tmp['role_ids']){
                    foreach ($data_tmp['role_ids'] as $key => $value) {
                       db("admin_role_relations")->insert(['role_id'=>$value,'admin_id'=>$res]);
                    }
                }
    			$this->return_data(1,'新增管理员成功');
    		}else{
    			$this->return_data(0,'新增管理员失败');
    		}
    	}
    	$this->assign('title','新增管理员');
        return $this->fetch();
    }

    public function edit(){
    	if(input('post.')){
    		$data = input('post.');
    		if(!$data['id']){
    			$this->return_data(0,'没有id');
    		}
    		if($data['ad_password']){
	    		if($data['ad_password'] != $data['ad_password_confirm']){
	    			$this->return_data(0,'密码不一致');
	    		}
	    		$data['ad_secret'] = mt_rand(10000,99999);
	    		$data['ad_password'] = sha1($data['ad_password'].$data['ad_secret']);
    		}else{
    			unset($data['ad_password']);
    		}
    		unset($data['ad_password_confirm']);
            if(isset($data['role_ids'])){
                $data_tmp['role_ids'] = $data['role_ids'];
                unset($data['role_ids']);
            }else{
                $data_tmp['role_ids'] = [];
            }
    		$res = db('admins')->data($data)->update();
            //插入角色
            db('admin_role_relations')->where(['admin_id'=>$data['id']])->delete();
            foreach ($data_tmp['role_ids'] as $key => $value) {
               db("admin_role_relations")->insert(['role_id'=>$value,'admin_id'=>$data['id']]);
            } 
   			$this->return_data(1,'编辑管理员成功');
    	}
    	$id = input('id/d');
    	if(!$id){
    		$this->error('没有id');
    	}
    	$admin = db('admins')->where(['id'=>$id])->find();
    	$this->assign('admin',$admin);
    	$this->assign('title','编辑管理员');
        //列出角色拥有的权限
        $has_roles = db('admin_role_relations')->where(['admin_id'=>$id])->column('role_id');
        $this->assign('has_roles',$has_roles);
        return $this->fetch();
    }
 

 
}
