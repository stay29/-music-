<?php
/*
 * teacher management;
 */

namespace app\admin\controller;


class Teacher extends AdminBase
{

    public function initialize(){
        $seniorities = db('seniorities')->field('seniority_id,seniority_name')->select();
        $this->assign('seniorities_list',$seniorities);
    }


    /*
     * show teacher
     */
    public function index()
    {
        $this->assign('title','教师列表');

        $teachers = db('teachers')->paginate(20)->each(function($v,$k){
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
            $account = db('users')->where(['uid'=>$v['manager']])->value('account');
            $v['manager'] = isset($account) ? $account : '';
            return $v;
        });
        $this->assign('teachers_list',$teachers);
        return $this->fetch();
    }

    // edit teacher info.
    public function edit(){
        if(input('post.')){
            $data = input('post.');
            if(!isset($data['has_imgs'])){
                $data['has_imgs'] = [];
            }
            $this->process_image_upload($data['avator'],'teacher_avator');
            $this->process_imags_problem($data['resume'],$data['has_imgs']);
            unset($data['has_imgs']);
            if(!$data['t_id']){
                $this->return_data(0,'没有t_id');
            }
            $data['entry_time'] = strtotime($data['entry_time']);
            $data['birthday'] = strtotime($data['birthday']);
            $data['resume'] = trim($data['resume']);
            db('teachers')->data($data)->update();
            $this->return_data(1,'编辑教师成功');
        }
        $id = input('t_id/d');
        if(!$id){
            $this->error('没有t_id');
        }
        $teacher = db('teachers')->where(['t_id'=>$id])->find();
        $teacher['avator'] = str_replace(DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $teacher['avator']);
        $teacher['resume'] = str_replace(DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $teacher['resume']);
        $teacher['curriculums'] = implode(',',db('curriculums')->alias('c')->join('erp2_cur_teacher_relations ctm','ctm.cur_id = c.cur_id')->column('cur_name'));
        $this->assign('teacher',$teacher);
        $this->assign('title','编辑教师');
        $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
        preg_match_all($preg, $teacher['resume'], $imgArr);
        $this->assign('imgArr',$imgArr[1]);
        return $this->fetch();
    }

    /*
     * delete teacher info.
     */
    public function del()
    {
        //
    }
}
