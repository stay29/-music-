<?php
namespace app\admin\controller; 

use app\admin\controller\Tool;
class System extends AdminBase
{
    public function initialize(){
      
    } 
    public function tool_index()
    {
        $this->assign('title','资源列表');
        $this->assign('add',url('tool_add'));

    	 $obj = new Tool();
         $data = $obj->index();
         $this->assign('tools_list',$data);
         return $this->fetch('tool/index');
    }

    public function tool_add(){
    	if(input('post.')){
    		$data = input('post.');     
    		if(db("tools")->where(['name'=>$data['name']])->count()){
    			$this->return_data(0,'该资源已存在');
    		} 
    		$data['create_time'] = $data['update_time'] = time(); $data['manager'] = session('admin.id');

    		$res = db('tools')->insertGetId($data);
    		if($res){
    			$this->return_data(1,'新增资源成功');
    		}else{
    			$this->return_data(0,'新增资源失败');
    		}
    	}
    	$this->assign('title','新增资源');
        return $this->fetch('tool/add');
    }

    public function tool_edit(){
    	if(input('post.')){
    		$data = input('post.');
    		if(!$data['id']){
    			$this->return_data(0,'没有id');
    		}

    		try{
                $data['update_time'] = time();
                db('tools')->data($data)->update();
                $this->return_data(1,'编辑资源成功');
            }catch (\Exception $e){
    		    $this->error('编辑资源失败');
            }
    	}
    	$id = input('id/d');
    	if(!$id){
    		$this->error('没有id');
    	}
    	$cates = db('tools')->where(['id'=>$id])->find();
    	$this->assign('tool',$cates);
    	$this->assign('title','编辑资源');
        return $this->fetch('tool/edit');
    }

     public function tool_del(){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        try{
            $res = db('tools')->where(['id'=>$id])->delete();
            if($res){
                $this->success('删除资源成功');
            }else{
                $this->error('删除资源失败');
            }
        }catch (\Exception $e){
            if($e->getMessage()){
                $this->error('有博文在使用该资源，请检查');
            }else{
                $this->success('删除资源成功');
            }
        }
    }
 
}
