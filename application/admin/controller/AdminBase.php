<?php
namespace app\admin\controller;
use think\Controller;
class AdminBase extends Controller
{
    public function __construct(){
         parent::__construct();
         //获取所有权限
         $this->assign('all_accesses',$this->make_tree($this->all_accesses()));
    }
    protected function all_accesses($type=1){
        if($type == 1){
            return db('admin_accesses')->field('aname,id,pid')->select();
        }elseif($type == 2){
            //全部权限
            return db('admin_accesses')->column('id');
        }elseif($type == 3){
            //部分权限
            return db('admin_role_relations')
                ->alias('arm')
                ->where(['arm.admin_id'=>session('admin.id')])
                ->join('erp2_admin_role_access_relations ram',' ram.role_id = arm.role_id')
                ->column('ram.access_id');
        }
    }
    protected $beforeActionList = [
        'first',//验证有没有登录了
        // 'second' =>  ['except'=>'hello'],
        // 'three'  =>  ['only'=>'hello,data'],
    ];

    public function first(){ 
        if(!session('?admin')){
            $this->redirect(url('admin/login/index'),302);
        }else{
              //当前页的节点,用于登录管理员验证是否有此权限
                 if(strpos($_SERVER['PATH_INFO'],'.')){
                     $this->page_access = substr($_SERVER['PATH_INFO'],0,strpos($_SERVER['PATH_INFO'],'.'));
                 }else{
                    $this->page_access = $_SERVER['PATH_INFO'];
                 }
                $a = explode('/',$this->page_access);
                $b = [$a[0],$a[1],$a[2],$a[3]];
                $this->page_access = implode('/',$b);
                 //获取登录管理的权限节点
                $adminid = session('admin.id');
                $this->admin_accesse_id = db('admin_role_relations')
                ->alias('arm')
                ->where(['arm.admin_id'=>$adminid])
                ->join('erp2_admin_role_access_relations ram',' ram.role_id = arm.role_id')
                ->column('ram.access_id');  
                $this->admin_accesse_aurl = db('admin_role_relations')
                ->alias('arm')
                ->where(['arm.admin_id'=>$adminid])
                ->join('erp2_admin_role_access_relations ram',' ram.role_id = arm.role_id')
                ->join('erp2_admin_accesses ac','ac.id = ram.access_id')
                ->where('ac.aurl','neq','')
                ->column('ac.aurl');  
                if(db('admin_accesses')->where(['aurl'=>$this->page_access])->count()){
                    if(!in_array($this->page_access,$this->admin_accesse_aurl) && session('admin.id') != 1){
                        $this->error('抱歉你没有该权限');
                    }
                }
                




        }
    }
 
 	/**
 	*响应
 	*$info,在status=1返回成功提示，0的时候返回错误提示，$data返回需要的数据
 	*/
 	public function return_data($status,$info,$data=''){
 		if($status){
 			$key = 'sinfo';
 		}else{
 			$key = 'emsg';
 		}
 		echo json_encode(['status'=>$status,$key =>$info,'data'=>$data]);die;
 	}
 	/**
 	*无限极分类
 	*/
 	 public function make_tree($arr,$pid=0,$level=1){
    	$return_data = [];
	      foreach ($arr as $k=>$v){
		        if ($v['pid'] == $pid){
		            $v['level']=$level;
		            $v['son'] = $this->make_tree($arr,$v['id'],$level+1);
		            $return_data[] = $v;
		        }
		    }
    	return $return_data;
    }
}
