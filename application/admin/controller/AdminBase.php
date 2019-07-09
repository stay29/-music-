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
                 $count = substr_count($_SERVER['PATH_INFO'],'/');
                 if($count >= 3){
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

    /*
     * 富文本图片上传重复公共处理器，供含有富文本的编辑操作过滤器
 	 * $str:富文本内容，
     * $arr:编辑页内容包含图片，需要正则匹配
 	 */
    public function process_imags_problem($str,$arr=[]){
        $editor = UPLOAD_DIR.'editor'.DIRECTORY_SEPARATOR;
        $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
        preg_match_all($preg, $str, $imgArr);

        if($imgArr[1]){
            $temp_dir = 'temp';
            if(!empty($imgArr[1])){
                $dest = [];
                foreach ($imgArr[1] as $key => $value) {
                    $v = strstr($value,'.');
                    if(file_exists($v)){
                        $destination = str_replace(DIRECTORY_SEPARATOR.$temp_dir,'',$v);
                        preg_match('/^.*(\d{4}-\d{1,2}-\d{1,2}).*$/', $destination, $day);
                        if(!is_dir($editor.$day[1])){
                            mkdir($editor.$day[1]);
                        }
                        if($v != $destination){
                            copy($v,$destination);
                            unlink($v);
                        }
                        $dest[] = $destination;
                    }
                }

                if($arr){
                    foreach ($arr as $key => $value) {
                        $value = strstr($value,'.');
                        if(!in_array($value,$dest)){
                            @unlink($value);
                        }
                    }
                }
                do_rmdir($editor.$temp_dir);
            }else{
                do_rmdir($editor.$temp_dir);
            }
        }else{
            if($arr){
                foreach ($arr as $key => $value) {
                    $value = strstr($value,'.');
                    if(file_exists($value)){
                        @unlink($value);
                    }
                }
            }
        }
    }

    //图片上传防止重复公共处理器，非富文本
    public function process_image_upload($temp_image,$dir=''){
        $temp_dir = 'temp';
        $temp_image = strstr($temp_image,'.');
        if(file_exists($temp_image)){
            $destination = str_replace(DIRECTORY_SEPARATOR.$temp_dir,'',$temp_image);
            preg_match('/^.*(\d{4}-\d{1,2}-\d{1,2}).*$/', $destination, $day);
            if(empty($dir)){
                if(!is_dir(UPLOAD_DIR.$day[1])){
                    mkdir(UPLOAD_DIR.$day[1],0777);
                }
            }else{
                if(!is_dir(UPLOAD_DIR.$dir.DIRECTORY_SEPARATOR.$day[1])){
                    mkdir(UPLOAD_DIR.$dir.DIRECTORY_SEPARATOR.$day[1],0777);
                }
            }

            if($temp_image != $destination){
                copy($temp_image,$destination);
                unlink($temp_image);
            }
        }
    }

}
