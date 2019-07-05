<?php
namespace app\admin\controller; 

class Access extends AdminBase
{

    public function initialize(){
        //获取父级权限
        $this->assign('parent_accesses',$this->parent_accesses());
    }

    private function parent_accesses(){
        return db('admin_accesses')->where('pid',0)->field('aname,id')->select();
    }
    public function index()
    {
    	$this->assign('title','权限列表');
		$this->assign('add',url('add'));

    	$accesses = db('admin_accesses')->select();

    	foreach ($accesses as $k => $v) {
    		if($v['status'] == 1){
    			$accesses[$k]['status_text'] = '正常';
    		}elseif($v['status'] == 2){
    			$accesses[$k]['status_text'] = '禁用';
    		}
    		$accesses[$k]['manager'] = db('admins')->where(['id'=>$v['manager']])->value('ad_account')??'超级权限';
    	}

    	$this->assign('accesses_list',$this->make_tree($accesses));
        return $this->fetch();
    }

    public function add(){
    	if(input('post.')){
    		$data = input('post.'); 
    		if(db("admin_accesses")->where(['aname'=>$data['aname']])->count()){
    			$this->return_data(0,'该权限已存在');
    		}
    		$data['create_time'] = $data['update_time'] = time();
    		$data['manager'] = session('admin.id');
    		$res = db('admin_accesses')->data($data)->insert();
    		if($res){
    			$this->return_data(1,'新增权限成功');
    		}else{
    			$this->return_data(0,'新增权限失败');
    		}
    	}
    	$this->assign('title','新增权限');
        return $this->fetch();
    }

    public function edit(){
    	if(input('post.')){
    		$data = input('post.'); 
            if(db("admin_accesses")->where(['aname'=>$data['aname']])->where('id','neq',$data['id'])->count()){
                $this->return_data(0,'该权限已存在');
            }
            $data['create_time'] = $data['update_time'] = time();
            $data['manager'] = session('admin.id');
            $res = db('admin_accesses')->data($data)->update();
            if($res){
                $this->return_data(1,'编辑权限成功');
            }else{
                $this->return_data(0,'编辑权限失败');
            }
    	}
    	$id = input('id/d');
    	if(!$id){
    		$this->error('没有id');
    	}
    	$admin = db('admin_accesses')->where(['id'=>$id])->find();
    	$this->assign('access',$admin);
    	$this->assign('title','编辑权限');
        return $this->fetch();
    }
    
    public function del(){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        try{
            $res = db('admin_accesses')->where(['id'=>$id])->delete();
            if($res){
                $this->success('删除权限成功');
            }else{
                $this->error('删除权限失败');
            }
        }catch (\Exception $e){
            if($e->getMessage()){
                $this->error('有角色使用该权限');
            }else{
                $this->success('删除权限成功');
            }

        }

    }
}
