<?php
/**
 * 教师
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */

namespace app\index\controller;
use app\admin\controller\AdminBase;
use app\index\model\Teacher as TeacherModel;
use think\Controller;
use think\Exception;

class Teacher extends BaseController
{
    /*
     * 教师列表
     */
    public function index()
    {
        $t_name = input('t_name/s', null); // 教师名称
        $se_id = input('se_id/s', null); // 机构ID
        $status = input('status/d', null);  // 离职状态
        $where = array();
        if(!empty($t_name))
        {
            $where[] = ['t_name', 'like', '%' . $t_name. '%'];
        }
        if(!empty($status))
        {
            $where[] = ['status', '=', $status];
        }
        if(!empty($se_id))
        {
            $where = ['se_id', '=', $se_id];
        }
        $teacher = TeacherModel::where($where)->field('t_id as id,t_name as name,
                sex,cellphone,birthday,entry_time,status, se_id, resume')->paginate(20);
        $this->return_data(1, '','', $teacher);
    }

    /*
     * 修改教师信息
     */
    public function edit()
    {

    }

    /*
     * 删除教师
     */
    public function del()
    {
        $t_id = input('t_id/d', null);
        if(empty($t_id))
        {
            $this->return_data(0, '10000', '缺少t_id');
        }
        try {

            $res = db('teachers')->where('t_id', '=', $t_id)->delete();
            if($res)
            {
                $this->return_data(1, '', '', '删除教师成功');
            }
            else
            {
                $this->return_data(0, '20003', '删除教师失败');
            }
        }catch (Exception $e)
        {
            $this->return_data(0, '20003', '删除教师失败');
        }
    }

    public function add(){

        $data = [
            't_name' => input('post.name'),
            'avator' => input('post.avator'),
            'sex' => input('post.sex',1),
            'se_id' => input('post.se_id'),
            'cellphone' => input('post.cellphone'),
            'birthday' => input('post.birthday'),
            'entry_time' => input('post.entry_day'),
            'resume' => input('post.resume'),
            'identity_card' => input('post.id_card'),
        ];

        $validate = new \app\index\validate\Teacher();
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }

        try{
            TeacherModel::create($data);
            $this->return_data(1,0,'教师新增成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }


    /*
     * 教师离职或者复职。 1离职, 2复职。
     */
    public function jobStatus()
    {
        $t_id = input('post.id');
        $status = input('post.status', null);
        if(empty($status) || empty($t_id))
        {
            $this->return_data(0, '10000', '缺少必填参数');
        }
        try
        {
            $res = TeacherModel::where('t_id', '=', $t_id)->update(['status'=>$status]);
            if($res)
            {
                $this->return_data(1, '', '操作成功');
            }
            else
            {
                $this->return_data(0, '', '操作失败');
            }
        }catch (Exception $e)
        {
                $this->return_data(0, '50000', '服务器错误');
        }

    }

    /*
     * 教师课表
     */
    public function LessonTable()
    {

    }
}


//class Teacher extends BaseController
//{
//    /**
//     * 我的查询，用于搜索
//     */
//    protected function _where($model){
//        if(!$model){
//            return '';
//        }
//        $teacher_name = input('get.teacher_name');
//        $status = input('get.status/d');
//        $se_id = input('get.se_id/d');
//        $status?$model->where('status',$status):'';
//        $se_id?$model->where('se_id',$se_id):'';
//        $teacher_name?$model->whereLike('t_name','%'.$teacher_name.'%'):'';
//
//        return $model;
//    }
//    /**
//     * 教师列表
//     */
//    public function get(){
//       $model = \app\index\model\Teacher
//            ::field('t_id as id,t_name as name,sex,cellphone,birthday,entry_time,status,se_id')
//            ->order('create_time desc');
//       $res = $this->_where($model)->paginate(20);
//
//       $this->return_data(1,0,'',$res);
//    }
//    /**
//     * 新增教师
//     */

//
//
//    /**
//     * 编辑教师
//     */
//    public function edit(){
//        $data = [
//            't_id'=>input('post.id'),
//            't_name' => input('post.name'),
//            'avator' => input('post.avator'),
//            'sex' => input('post.sex',1),
//            'se_id' => input('post.se_id'),
//            'cellphone' => input('post.cellphone'),
//            'birthday' => input('post.birthday'),
//            'entry_time' => input('post.entry_day'),
//            'resume' => input('post.resume'),
//            'identity_card' => input('post.id_card')
//        ];
//
//        $validate = new \app\index\validate\Teacher();
//        if (!$validate->check($data)) {
//            //为了可以得到错误码
//            $error = explode('|',$validate->getError());
//            $this->return_data(0,$error[1],$error[0]);
//        }
//        try{
//            \app\index\model\Teacher::update($data,['t_id'=>$data['t_id']]);
//            $this->return_data(1,0,'编辑教师成功');
//        }catch (\Exception $e){
//            $this->return_data(0,50000,$e->getMessage());
//        }
//    }
//
//    /**
//     * 设置某些字段，如离职
//     */
//    public function set_field(){
//        $id = input('id/d');
//        $field = input('field');
//        $action = input('action');
//        $data = [
//            't_id'=>$id,
//            'field'=>$field,
//            'action'=>$action
//        ];
//        $validate = new \app\index\validate\Teacher();
//        if(!$validate->scene('field')->check($data)){
//            //为了可以得到错误码
//            $error = explode('|',$validate->getError());
//            $this->return_data(0,$error[1],$error[0]);
//        }
//        $sinfo = '';
//        try{
//            switch ($field){
//                case 1://离职
//                    $field = 'status';
//                    $sinfo = '离职成功';
//                    break;
//            }
//            \app\index\model\Teacher::where('t_id',$id)->update([$field=>$action]);
//            $this->return_data(1,0,$sinfo);
//        }catch (\Exception $e){
//            $this->return_data(0,50000,$e->getMessage());
//        }
//
//
//    }
//}