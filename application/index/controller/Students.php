<?php

namespace app\index\controller;

use MongoDB\BSON\Decimal128;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;
use app\index\validate\Students as StuValidate;
use app\index\model\Students as StuModel;
use app\index\model\StuBalance;

class Students extends BaseController
{

    /*
     * 学生列表
     */
    public function index()
    {
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        $org_id = input('orgid/d', '');
        $stu_name = input('stu_name/s', '');
        $t_name = input('t_name/s', '');
        $cur_name = input('cur_name/s', '');
        if (is_empty($org_id)) {
            $this->returnError(10000, '缺少参数');
        }
        $tb = db('students')->where(['org_id'=>$org_id]);
        if (!empty($stu_name))
        {
            $tb->where('truename', 'like', '%' . $stu_name . '%');
        }
        $data = $tb->select();
        if (!empty($t_name))
        {
            $t_id = db('teachers')->where(['t_id', 'like', '%' . $t_name . '%'])->value('t_id');
            $t_id = $t_id ? $t_id : -1;
            $sql = "SELECT * FROM erp2_students WHERE stu_id IN 
                    (SELECT stu_id FROM erp2_classes_teachers_realations
                     AS A INNER JOIN erp2_class_student_relations AS 
                     B ON A.cls_id=B.class_id WHERE A.t_id={$t_id});";
            array_push($data, Db::query($sql));
        }
        if (!empty($cur_name))
        {
            $cur_id = db('curriculums')->where('cur_name', 'like', '%'.$cur_name.'%')->value('cur_id');
            $cur_id = $cur_id ? $cur_id : -1;
            $sql = "SELECT * FROM erp2_students WHERE stu_id IN (SELECT stu_id FROM erp2_stu_cur WHERE cur_id={$cur_id});";
            array_push($data, Db::query($sql));
        }
        $res_data = [];
        foreach ($data as $k=>$v)
        {
            $stu_id = $v['stu_id'];
            $cur_sql = "SELECT cur_name FROM erp2_curriculums WHERE cur_id IN (SELECT cur_id FROM erp2_stu_cur WHERE stu_id={$stu_id});";
            $cur_list = Db::query($cur_sql);
            $t_sql = "SELECT t_name FROM erp2_teachers WHERE t_id IN 
(SELECT t_id FROM erp2_classes_teachers_realations
 AS A INNER JOIN erp2_class_student_relations AS 
 B ON A.cls_id=B.class_id WHERE B.stu_id={$stu_id})";
            $res = Db::query($t_sql);
            $t_name = empty($res) ? '' : $res[0]['t_name'];
            // 已购课程
            $already_buy = db('teach_schedules')->where(['status'=>1, 'stu_id'=>$stu_id])->count('*');
            // 已上课程
            $already_done = db('teach_schedules')->where(['status'=>2, 'stu_id'=>$stu_id])->count('*');

            $surplus_lesson = 0;    // 剩余课程写死为0
            $res_data[] = [
                'stu_id' => $stu_id,
                'stu_name' => $v['truename'],
                'mobile'    => $v['cellphone'],
                'cur_list'  => $cur_list,
                't_name'    => $t_name,
                'already_done' => $already_done,
                'already_buy'   => $already_buy,
                'surplus_lesson' => $surplus_lesson,
            ];
        }
        $response = [
            'per_page' => $page,
            'total' => count($res_data),
            'last_page' => intval(count($res_data)) / $limit + 1,
            'data' => array_slice($res_data, ($page - 1)*$limit, $limit),
        ];
        $this->returnData($response, '请求成功');
    }

}

