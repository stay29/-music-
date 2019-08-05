<?php

namespace app\index\controller;

use MongoDB\BSON\Decimal128;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;
use app\index\validate\Students as StuValidate;
use app\index\model\Students as StuModel;

/*
 * Student-related Functional Controller.
 */


class Students extends BaseController
{
    /*
     * Show students records filter by status.
     */
    public function index()
    {
        $status = input('status', '');
        $org_id = input('org_id', '');
        $limit = input('limit', 10);
        $stu_name = input('stu_name', '');
        $teacher_name = input('t_name', '');
        $course_name = input('c_name', '');
        $where[] = ['org_id', '=', $orgid];

        if(empty($orgid))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        if (!empty($status))
        {
            $where[] = ['status', '=', $status];
        }
        if (!empty($stu_name))
        {
            $where[] = ['stu_name', 'like', '%' . $stu_name . '%'];
        }
        if (!empty($teacher_name))
        {
            $where[] = ['teacher_name', 'like', '%' . $teacher_name . '%'];
        }
        if (!empty($course_name))
        {
            $where[] = ['cur_name', 'like', '%' . $course_name . '%'];
        }
        $where[] = ['is_del', '=', 0];
        $where[] = ['org_id', '=', $org_id];
        $students = db('students')->field('stu_id, truename as stu_name, sex, birthday,
                cellphone, wechat, address, remark')->where($where)->paginate($limit);
        $this->return_data(1, '', '', $students);
    }

    /*
     * Modify Student Records
     */
    public function edit()
    {
        $data = input();
        if(!$this->request->isPost())
        {
            $this->return_data(0, '10007', '请用post方法提交数据');
        }

        $validate = new StuValidate();
        if(!$validate->scene('edit')->check($data)){
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try
        {
            $stu = StuModel::update($data);
            $stu->save();
            $this->return_data(1, '', '添加学生成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, 50000, '服务器错误', false);
        }
    }

    /*
     * Delete Students Records
     */
    public function del()
    {
        $stu_id = input('stu_id', '');
        $org_id = input('org_id', '');
        if(empty($stu_id) || empty($org_id))
        {
            $this->return_data(0, 10000, '缺少请求参数stu_id或org_id');
        }
        try
        {
            StuModel::where('stu_id', '=', $stu_id)->update(['is_del'=>1]);
            $this->return_data(1, '', '删除成功', true);
        }catch (Exception $e){
            $this->return_data(0, '', '删除失败', false);
        }
    }

    /*
     * Student Class Information
     */
    public function classInfo()
    {
        $stu_id = input('stu_id', '');
        $org_id = input('org_id', '');
        $page = input('page', 1);
        $pageSize = input('pageSize', 10);
        if (empty($stu_id) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少stu_id或org_id');
        }
        $start = ($page - 1) * $pageSize;
        $end = $pageSize;
        $sql = "SELECT B.class_id AS cls_id, B.class_name AS cls_name 
	FROM erp2_class_student_relations AS A 
    INNER JOIN erp2_classes AS B ON A.class_id = B.class_id WHERE stu_id=1 LIMIT {$start} OFFSET {$end}";
        $data = Db::query($sql);
        $this->return_data(1, '', '请求成功', $data);
    }

    /*
     * Replacement of student classes.
     */
    public function changeClass()
    {
        $stu_id = input('stu_id/d', '');  // student's id.
        $org_id = input('org_id/d', '');  // student's organization id.
        $cls_id = input('cls_id/d', ''); // student's original class id.
        $new_cls_id = input('new_cls_id/d', ''); // student's new class id.
        if (empty($stu_id) || empty($cls_id) || empty($new_cls_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        Db::startTrans();
        try {
            $where[] = ['stu_id', '=', $stu_id];
            $where[] = ['cls_id', '=', $cls_id];
            $where[] = ['org_id', '=', $org_id];
            $data = ['cls_id'=>$cls_id];
            Db::table('erp2_class_student_relations')->where($where)->update($data);
            Db::commit();
            $this->return_data(1, '', '更换教室成功', true);
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data(0, '', '更换教室失败', false);
        }
    }

    /*
     * Creating Student Records
     */
    public function add()
    {

        if(!$this->request->isPost())
        {
            $this->return_data(0, '10007', '请用post方法提交数据');
        }
        $data = input();
//        var_dump($data);
        $validate = new StuValidate();
        // validate data.
        if(!$validate->scene('add')->check($data)){
//            var_dump($validate->getError());
            $error = explode('|', $validate->getError());
//            var_dump($error);
            $this->return_data(0, $error[1], $error[0]);
        }
        try
        {
            $stu = StuModel::create($data);
            $stu->save();
            $this->return_data(1, '', '添加学生成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, 50000, '服务器错误', false);
        }
    }

    /**
     * Return data of student schedule
     */
    public function schedule()
    {
        $stu_id = input('stu_id', '');
        $org_id = input('orgid', '');
        if(empty($stu_id) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }

    }

}


/**
 * The student balance log becomes recorded
 * @package app\index\controller
 */
Class BalanceLog{
    /*
     * recharge record method.
     */
    public static function rechargeRecord($money){
        $data = [
            'op_id' => 1,
            ''
        ];
    }
}