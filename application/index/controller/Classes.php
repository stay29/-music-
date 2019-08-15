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
    public function addclasses()
    {
        $cls_id = input('cls_id');
        $stu_id = input('stu_id');
        $data = [
            'class_name'=>input('class_name'),
            'class_count'=>input('class_count'),
            'headmaster'=>input('headmaster'),
            'remarks'=>input('remarks'),
            'orgid' =>ret_session_name('orgid'),
            'manager' =>ret_session_name('uid'),
            'status'   =>1,
            'create_time' =>time(),
            'update_time' => time(),
        ];
         Db::startTrans();
         try{

            
        $res = add('erp2_classes',$data,2);

         Db::commit();
        if($res){
        $data1 = [
            'cls_id'=>$cls_id,
            'cur_id'=>$res,
        ];
        $res1 = add('erp2_class_cur',$data1);
        if($res1){
        $aa = [];
        if(!empty($stu_id)){
            foreach ($stu_id as $k=>&$v){
                $aa['stu_id'] = $v;
                $aa['cls_id'] = $res;
            }
        }
        $res2 =  Db::name('erp2_class_student_relations')->insertAll($aa);
        if($res2){
            $this->return_data(1,0,'添加成功');
        }else{
            $this->return_data(0,10000,'操作失败');
        }
        }else{
            $this->return_data(0,10000,'操作失败');
        }
        }else{
            $this->return_data(0,10000,'操作失败');
        }
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    public function  classes_list()
    {
            echo "111";exit();
    }





}