<?php
namespace app\admin\controller; 

use think\Exception;

class Role extends AdminBase
{
    public function initialize(){
      
    }
    public function index()
    {
    	$this->assign('title','角色列表');
		$this->assign('add',url('add'));
    	$roles = db('admin_roles')->paginate(20)->each(function($v,$k){
            if($v['status'] == 1){
                $v['status_text'] = '正常';
            }elseif($v['status'] == 2){
                $v['status_text'] = '禁用';
            }
            $v['manager'] = db('admins')->where(['id'=>$v['manager']])->value('ad_account')??'超级角色';
            return $v;
        });
    	foreach ($roles as $k => $v) {
    	}
    	$this->assign('roles_list',$roles);
        return $this->fetch();
    }
    public function add(){
    	if(input('post.')){
    		$data = input('post.');     
    		if(db("admin_roles")->where(['rname'=>$data['rname']])->count()){
    			$this->return_data(0,'该角色已存在');
    		} 
    		$data['create_time'] = $data['update_time'] = time(); $data['manager'] = session('admin.id');
            $data_tmp['access_ids'] = $data['access_ids'];
            unset($data['access_ids']);
    		$res = db('admin_roles')->insertGetId($data);
    		if($res){
                if($data_tmp['access_ids']){
                    foreach ($data_tmp['access_ids'] as $key => $value) {
                         db('admin_role_access_relations')->insert(['access_id'=>$value,'role_id'=>$res]);
                         $shuju[] = ['access_id'=>$value,'role_id'=>$res];
                    }
  
                    file_put_contents('debug.txt',var_export($shuju,true));
                }
    			$this->return_data(1,'新增角色成功');
    		}else{
    			$this->return_data(0,'新增角色失败');
    		}
    	}
    	$this->assign('title','新增角色');
        return $this->fetch();
    }
    public function edit(){
    	if(input('post.')){
    		$data = input('post.');
    		if(!$data['id']){
    			$this->return_data(0,'没有id');
    		} 
            $data_tmp['access_ids'] = $data['access_ids'];
            unset($data['access_ids']);
    		$res = db('admin_roles')->data($data)->update();
            db('admin_role_access_relations')->where('role_id',$data['id'])->delete();
            foreach ($data_tmp['access_ids'] as $key => $value) {
                db('admin_role_access_relations')->insert(['access_id'=>$value,'role_id'=>$data['id']]);
            }
            $this->return_data(1,'编辑角色成功');
    	}
    	$id = input('id/d');
    	if(!$id){
    		$this->error('没有id');
    	}
    	$roles = db('admin_roles')->where(['id'=>$id])->find();
    	$this->assign('role',$roles);
    	$this->assign('title','编辑角色');
        //列出角色拥有的权限
        $has_accesses = db('admin_role_access_relations')->where(['role_id'=>$id])->column('access_id');
        $this->assign('has_accesses',$has_accesses);
        return $this->fetch();
    }
     public function del(){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        try{
            $res = db('admin_roles')->where(['id'=>$id])->delete();
            if($res){
                $this->success('删除角色成功');
            }else{
                $this->error('删除角色失败');
            }
        }catch (\Exception $e){
            if($e->getMessage()){
                $this->error('有用户在使用该角色，请检查');
            }else{
                $this->success('删除角色成功');
            }

        }

    }
 
}
