<?php
namespace app\admin\controller; 

use app\admin\controller\Tool;
class System extends AdminBase
{
    public function initialize(){
    } 
    public function teacher_seniory()
    {
         $this->assign('title','资历列表');
         $this->assign('add',url('seniory_add'));
         $data = db('seniorities')->
         field('seniority_name,seniority_id,status,manager,systemed,update_time')->where('is_del', '=', 0)->paginate(20,false,['query'=>request()->param()])->each(function($v,$k){
             if($v['status'] == 1){
                 $v['status_text'] = '正常';
             }elseif($v['status'] == 2){
                 $v['status_text'] = '已禁用';
             }
             $account = db('users')->where(['uid'=>$v['manager']])->value('account');
             $v['manager'] = isset($account) ? $account : '系统';
             return $v;
         });
         $this->assign('teacher_seniory_list',$data);
         return $this->fetch();
    }

    public function seniory_add(){
    	if(input('post.')){
    		$data = input('post.');     
    		if(db("seniorities")->where(['seniority_name'=>$data['seniority_name']])->count()){
    			$this->return_data(0,'该资历已存在');
    		} 
    		$data['create_time'] = $data['update_time'] = time(); $data['manager'] = session('admin.id');
    		$data['is_official'] = 1;
    		$res = db('seniorities')->insertGetId($data);
    		if($res){
    			$this->return_data(1,'新增资历成功');
    		}else{
    			$this->return_data(0,'新增资历失败');
    		}
    	}
    	$this->assign('title','新增资历');
        return $this->fetch();
    }

    public function seniory_edit(){
    	if(input('post.')){
    		$data = input('post.');
    		if(!$data['id']){
    			$this->return_data(0,'没有id');
    		}

    		try{
                $data['update_time'] = time();
                $data['seniority_id'] = $data['id'];
                unset($data['id']);
                db('seniorities')->data($data)->update();
                $this->return_data(1,'编辑资历成功');
            }catch (\Exception $e){
    		    $this->error('编辑资历失败');
            }
    	}
    	$id = input('id/d');
    	if(!$id){
    		$this->error('没有id');
    	}
    	$seniority = db('seniorities')->where(['seniority_id'=>$id])->find();
    	$this->assign('seniority',$seniority);
    	$this->assign('title','编辑资历');
        return $this->fetch();
    }

     public function seniory_del(){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        try{
            $res = db('seniorities')->where(['seniority_id'=>$id])->delete();
            if($res){
                $this->success('删除资历成功');
            }else{
                $this->error('删除资历失败');
            }
        }catch (\Exception $e){
            if($e->getMessage()){
                $this->error('有教师在使用该资历，无法删除');
            }else{
                $this->success('删除资历成功');
            }
        }
    }

    public function payments()
    {
        $this->assign('title','支付方式列表');
        $this->assign('add',url('payment_add'));
        $data = db('payments')->field('payment_method,pay_id,status,manager,create_time,update_time')->paginate(10,false,['query'=>request()->param()])->each(function($v,$k){
            if($v['status'] == 1){
                $v['status_text'] = '正常';
            }elseif($v['status'] == 2){
                $v['status_text'] = '已禁用';
            }

            $account = db('admins')->where(['id'=>$v['manager']])->value('ad_account');
            $v['manager'] = isset($account) ? $account : '未知';
            return $v;
        });
        $this->assign('payments_list',$data);
        return $this->fetch();
    }

    public function payment_add(){
        if(input('post.')){
            $data = input('post.');
            if(db("payments")->where(['payment_method'=>$data['payment_method']])->count()){
                $this->return_data(0,'该支付方式已存在');
            }
            $data['create_time'] = $data['update_time'] = time(); $data['manager'] = session('admin.id');

            $res = db('payments')->insertGetId($data);
            if($res){
                $this->return_data(1,'新增支付方式成功');
            }else{
                $this->return_data(0,'新增支付方式失败');
            }
        }
        $this->assign('title','新增支付方式');
        return $this->fetch();
    }
    public function payment_edit(){
        if(input('post.')){
            $data = input('post.');
            if(!$data['id']){
                $this->return_data(0,'没有id');
            }

            try{
                $data['update_time'] = time();
                $data['pay_id'] = $data['id'];
                unset($data['id']);
                db('payments')->data($data)->update();
                $this->return_data(1,'编辑支付方式成功');
            }catch (\Exception $e){
                $this->error('编辑支付方式失败');
            }
        }
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        $payment = db('payments')->where(['pay_id'=>$id])->find();
        $this->assign('payment',$payment);
        $this->assign('title','编辑支付方式');
        return $this->fetch();
    }


    public function payment_del(){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        try{
            $res = db('payments')->where(['pay_id'=>$id])->delete();
            if($res){
                $this->success('删除支付方式成功');
            }else{
                $this->error('删除支付方式失败');
            }
        }catch (\Exception $e){
            if($e->getMessage()){
                $this->error('有教师在使用该支付方式，无法删除');
            }else{
                $this->success('删除支付方式成功');
            }
        }
    }










}
