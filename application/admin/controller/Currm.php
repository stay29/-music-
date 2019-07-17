<?php
namespace app\admin\controller; 
use think\Exception;
class Currm extends AdminBase
{
    public function index()
    {
       
       $this->assign('title','课程列表');
       //$this->assign('add',url('add'));
       $users_list   = db('curriculums')->paginate(20)->each(function($v,&$k){
            if($v['state'] == 1){
                $v['state'] = '上架';
            }elseif($v['state'] == 2){
                $v['state'] = '下架';
            }
            $admins =  db('admins')->where(['id'=>$v['manager']])->value('ad_account');
            if($admins){
                $v['manager'] = $admins;
            }
            return $v;
        });
       $this->assign('users_list',$users_list);
        return view();
    }
    //课程编辑
    public function edit(){
         $cur_id['cur_id'] = input('cur_id');
        if(input('post.')){
            $data = input('post.');
            $data['cur_id'] = $data['cur_id']; 
             db('curriculums')->data($data)->update();
                $this->return_data(1,'编辑课程成功'); 
        }
        $curriculums = db('curriculums')->where($cur_id)->find();
        $subjects = db('subjects')->select();
        $this->assign('res',$curriculums);
        $this->assign('subjects',$subjects);
        $this->assign('title','编辑课程');
        return $this->fetch();
    }

    //课程科目
    public function subjects()
    {
        $this->assign('title','课程科目列表');
        $this->assign('add',url('subject_add'));
        $data = db('subjects')->field('sname,sid,status,manager,create_time,update_time,pid')->paginate(20,false,['query'=>request()->param()])->each(function($v,$k){
            if($v['status'] == 1){
                $v['status_text'] = '正常';
            }elseif($v['status'] == 2){
                $v['status_text'] = '已禁用';
            }
            if($v['pid']==0){
                $v['pid'] = '顶级分类';
            }else{
                $v['pid'] =  db('subjects')->where(['sid'=>$v['pid']])->value('sname');
            }
            $account = db('admins')->where(['id'=>$v['manager']])->value('ad_account');
            $v['manager'] = isset($account) ? $account : '未知';
            return $v;
        });
        $this->assign('subjects_list',$data);
        return $this->fetch();
    }

    public function subject_add(){
        if(input('post.')){
            $data = input('post.');
            if(db("subjects")->where(['sname'=>$data['sname']])->count()){
                $this->return_data(0,'该课程科目已存在');
            }
            $data['create_time'] = $data['update_time'] = time(); $data['manager'] = session('admin.id');
            $res = db('subjects')->insertGetId($data);
            if($res){
                $this->return_data(1,'新增课程科目成功');
            }else{
                $this->return_data(0,'新增课程科目失败');
            }
        }
        $sublist = db('subjects')->where('pid',0)->select();
        $this->assign('sublist',$sublist);
        $this->assign('title','新增课程科目');
        return $this->fetch();
    }
    public function subject_edit(){
        if(input('post.')){
            $data = input('post.');
            if(!$data['id']){
                $this->return_data(0,'没有id');
            }

            try{
                $data['update_time'] = time();
                $data['sid'] = $data['id'];
                unset($data['id']);
                db('subjects')->data($data)->update();
                $this->return_data(1,'编辑课程科目成功');
            }catch (\Exception $e){
                $this->error('编辑课程科目失败');
            }
        }
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        $sublist = db('subjects')->where('pid',0)->select();
        $this->assign('sublist',$sublist);
        $subject = db('subjects')->where(['sid'=>$id])->find();
        $this->assign('subject',$subject);
        $this->assign('title','编辑课程科目');
        return $this->fetch();
    }

    public function subject_del(){
        $id = input('id/d');
        if(!$id){
            $this->error('没有id');
        }
        try{
            $res = db('subjects')->where(['sid'=>$id])->delete();
            if($res){
                $this->success('删除课程科目成功');
            }else{
                $this->error('删除课程科目失败');
            }
        }catch (\Exception $e){
            if($e->getMessage()){
                $this->error('有教师在使用该课程科目，无法删除');
            }else{
                $this->success('删除课程科目成功');
            }
        }
    }
 
}
