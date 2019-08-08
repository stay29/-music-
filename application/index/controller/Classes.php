<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-8
 * Time: 下午3:07
 */

namespace app\index\controller;


use think\Controller;
use think\Db;

class Classes extends BaseController
{
    /*
     * 班级列表
     */
    public function index()
    {
        $cls_name = input('cls_name/s', '');
        $limit = input('limit/d', 10);
        $page = input('page/d', 1);
        $start = ($page - 1) * $limit;
        if (!empty($cls_name))
        {
            $key = '%' . $cls_name . '%';
        }
        else
        {
            $key = '%';
        }
        $sql = "SELECT A.class_id AS cls_id, A.class_name,C.cur_name, A.class_count, 
                count(D.class_id) AS cur_count, F.t_name AS headmaster, A.remarks 
                FROM (erp2_classes AS A 
	            INNER JOIN erp2_class_cur AS B ON A.class_id=B.cls_id)
	            LEFT JOIN erp2_curriculums AS C ON B.cur_id=C.cur_id
	            LEFT JOIN erp2_class_student_relations AS D ON A.class_id=D.class_id 
	            INNER join erp2_teachers AS F ON A.headmaster = F.t_id
	            WHERE A.class_name LIKE {$key} AND A.is_del=0 LIMIT {$start}, {$limit};";
        $data = Db::query($sql);
        if (count($data) == 1 && empty($data[0]['cls_id']))
        {
            $data = [];
        }
        $this->return_data(1, '', '请求成功', $data);

    }

    /*
     * 添加班级
     */
    public function add()
    {

    }
}