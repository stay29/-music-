<?php
namespace app\admin\controller; 

class User extends AdminBase
{

    public function initialize(){
        //教师资历
        $seniorities = db('seniorities')->field('seniority_id,seniority_name')->select();
        $this->assign('seniorities_list',$seniorities);
    }
    public function index()
    { 
    	$this->assign('title','用户列表');
		$this->assign('add',url('add'));

    	$users = db('users')->paginate(20)->each(function($v,$k){
            if($v['status'] == 1){
                $v['status_text'] = '正常';
            }elseif($v['status'] == 2){
                $v['status_text'] = '已拉黑';
            }
            if($v['sex'] == 1){
                $v['sex'] = '男';
            }elseif($v['sex'] == 2){
                $v['sex'] = '女';
            }
                // $v['manager'] = db('users')->where(['id'=>$v['manager']])->value('account')??'';
            $v['manager'] = '';
            return $v;
        });
 
    	$this->assign('users_list',$users);
        return $this->fetch();
    }

    public function add(){
    	if(input('post.')){
    		$data = input('post.');
    		if($data['password'] != $data['password_confirm']){
    			$this->return_data(0,'密码不一致');
    		}
    		unset($data['password_confirm']);
    		if(db("users")->where(['account'=>$data['account']])->count()){
    			$this->return_data(0,'该用户已存在');
    		}
    		$data['password'] = md5($data['password']);
    		$data['create_time'] = $data['update_time'] = time();
    		$data['manager'] = session('admin.id');
          
    		$res = db('users')->data($data)->insert();
    		if($res){
                 
    			$this->return_data(1,'新增用户成功');
    		}else{
    			$this->return_data(0,'新增用户失败');
    		}
    	}
    	$this->assign('title','新增用户');
        return $this->fetch();
    }

    public function edit(){
    	if(input('post.')){
    		$data = input('post.');
    		if(!$data['uid']){
    			$this->return_data(0,'没有uid');
    		}
    		if($data['password']){
	    		if($data['password'] != $data['password_confirm']){
	    			$this->return_data(0,'密码不一致');
	    		}
	    		$data['password'] = md5($data['password']);
    		}else{
    			unset($data['password']);
    		}
    		unset($data['password_confirm']);
            
    		db('users')->data($data)->update();
             
   			$this->return_data(1,'编辑用户成功');
    	}
    	$id = input('uid/d');
    	if(!$id){
    		$this->error('没有uid');
    	}
    	$user = db('users')->where(['uid'=>$id])->find();
    	$this->assign('user',$user);
    	$this->assign('title','编辑用户');
        return $this->fetch();
    }

    //学生列表
    public function student(){
        $this->assign('title','学生列表');
        $students_list   = db('students')->paginate(20)->each(function($v,$k){
            if($v['status'] == 1){
                $v['status_text'] = '就读';
            }elseif($v['status'] == 2){
                $v['status_text'] = '离校';
            }
            $account = db('users')->where(['uid'=>$v['manager']])->value('account');
            $v['manager'] = isset($account) ? $account : '';
            return $v;
        });
        $this->assign('students_list',$students_list);
        return view();
    }
    //教师编辑
    public function student_edit(){
        if(input('post.')){
            $data = input('post.');
            $this->process_image_upload($data['avator'],'student_avator');

            if(!$data['stu_id']){
                $this->return_data(0,'没有stu_id');
            }
            $data['birthday'] = strtotime($data['birthday']);
            db('students')->data($data)->update();
            $this->return_data(1,'编辑学生成功');
        }
        $id = input('stu_id/d');
        if(!$id){
            $this->error('没有t_id');
        }
        $student= db('students')->where(['stu_id'=>$id])->find();
        $student['avator'] = str_replace(DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $student['avator']);
        $this->assign('student',$student);
        $this->assign('title','编辑学生');
        return $this->fetch();
    }
}
