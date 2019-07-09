<?php
namespace app\admin\controller; 
use think\Exception;
class Organization extends AdminBase
{
    public function index()
    {
       $this->assign('title','机构列表');
       //$this->assign('add',url('add'));
       $org_list   = db('organizations')->paginate(20);
       $this->assign('org_list',$org_list);
        return view();
    }

        public function edit(){
    	$this->assign('title','机构编辑');
    	$mup['or_id'] =  $id = input('or_id/d');
    	$res = db('organizations')->where($mup)->find();
        $this->assign('res',$res);
        return view();
        }

    public function editon(){
    	$mup['or_id'] =  $id = input('or_id/d');
    	$data =input('');
    	$res = db('organizations')->where($mup)->data($data)->update();
    	if ($res) {
    		echo '1';
    	}else{
    		echo '更新失败';
    	}
    }

}