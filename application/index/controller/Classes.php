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
use Think\Exception;

class Classes extends BaseController
{
    /*
     * 添加班级
     */
    public function addclasses()
    {
        /*
         * qiu jian de gu fu sheng gong.
         */
        //$this->auth_get_token();
        //print_r(input('post.'));exit();
//        $cur_id = input('cur_id');
//        $stu_id = input('stu_id');
        $data = [
            'class_name' => input('class_name'),
            'class_count' => input('class_count'),
            'headmaster' => input('headmaster'),
            'remarks' => input('remarks'),
            'orgid' => ret_session_name('orgid'),
            'manager' => ret_session_name('uid'),
            'status' => 1,
            'create_time' => time(),
            'update_time' => time(),
            'is_del' => 0,
        ];
//        Db::startTrans();
//        try {
//            $validate = new \app\index\validate\Classes();
//            if (!$validate->scene('add')->check($data)) {
//                //为了可以得到错误码
//                $error = explode('|', $validate->getError());
//                $this->return_data(0, $error[1], $error[0]);
//            }
//            $res = add('erp2_classes', $data, 2);
//            Db::commit();
//            if ($res and !empty($cur_id)) {
//                $data1 = [
//                    'cls_id' => $res,
//                    'cur_id' => $cur_id,
//                ];
//                $res1 = add('erp2_class_cur', $data1, 1);
//                //$res1 =    Db::table('erp2_class_cur')->insertAll($aa2);
//                if ($res1) {
//                    $aa1 = [];
//                    if (!empty($stu_id)) {
//                        foreach ($stu_id as $k => &$v) {
//                            $aa['stu_id'] = $v;
//                            $aa['class_id'] = $res;
//                            $aa['is_del'] = 0;
//                            $aa1[] = $aa;
//                        }
//                    }
//                    $res2 = Db::table('erp2_class_student_relations')->insertAll($aa1);
//                    //$asss = Db::table('erp2_class_student_relations')->getLastSql();
//                    if ($res2) {
//                        $this->return_data(1, 0, '添加成功');
//                    } else {
//                        $this->return_data(0, 10000, '操作失败1');
//                    }
//                } else {
//                    $this->return_data(0, 10000, '操作失败2');
//                }
//            } else {
//                $this->return_data(0, 10000, '操作失败3');
//            }
//        } catch (\Exception $e) {
//            // 回滚事务
//            Db::rollback();
//            $this->return_data(0, 50000, $e->getMessage());
//        }
        /*wo bu xiang kan shang mian de dai ma*/
        $cur_id = input('cls_id/d');
        $stu_id_list = input('stu_id');

        $data = [
            'class_name' => input('class_name'),
            'class_count' => input('class_count'),
            'headmaster' => input('headmaster'),
            'remarks' => input('remarks'),
            'orgid' => ret_session_name('orgid'),
            'manager' => ret_session_name('uid'),
            'status' => 1,
            'create_time' => time(),
            'update_time' => time(),
            'is_del' => 0,
        ];
        $validate = new \app\index\validate\Classes();
        if (!$validate->scene('add')->check($data))
        {
            $errors = $validate->getError();
            $this->returnError($errors[1], $errors[0]);
        }
        Db::startTrans();
        try
        {
            $cls_id = Db::name('classes')->insertGetId($data);
            if (!$cls_id)
            {
                $this->returnError('20001', '操作失败');
            }
            if (!empty($cur_id))
            {
                Db::name('class_cur')->insert(['cur_id' => $cur_id, 'cls_id' => $cls_id]);
            }
            $in_data = [];
            for ($i = 0; $i < count($stu_id_list); $i++)
            {
                $stu_id = $stu_id_list[$i];
                $in_data[$i] = [
                  'class_id' => $cls_id,
                  'stu_id' => $stu_id
                ];
            }
            if (!empty($in_data))
            {
                Db::name('class_student_relations')->insertAll($in_data);
            }
            Db::commit();
            $this->returnData('', '添加成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError('50000', '操作失败');
        }
    }


    public function classes_list()
    {
        $page = input('page');
        if ($page == null) {
            $page = 1;
        }
        $limit = input('limit');
        if ($limit == null) {
            $limit = 10;
        }
        $where[] = ['status', '=', 1];
        $where[] = ['orgid', '=', input('orgid')];
        $where[] = ['is_del', '=', 0];
        $class_name = input('cls_name');
        if ($class_name != null) {
            $where[] = ['class_name', 'like', '%' . $class_name . '%'];
        }
        $res = selects('erp2_classes', $where);
        foreach ($res as $k => &$v) {
            $v['headmasterinfo'] = finds('erp2_teachers', ['t_id' => $v['headmaster']]);
            $v['orginfo'] = finds('erp2_organizations', ['or_id' => input('orgid')]);
            //$v['currlist'] = Db::table('erp2_class_cur')->alias('c')->where(['cls_id'=>$v['class_id']])->join('erp2_curriculums r','c.cur_id=r.cur_id')->select();
            $v['currlist'] = Db::table('erp2_class_cur')->alias('c')->where(['cls_id' => $v['class_id']])->join('erp2_curriculums r', 'c.cur_id=r.cur_id')->find();
            $cheadmaster = selects('erp2_class_student_relations', ['class_id' => $v['class_id']]);
            $v['cheadmaster'] = count($cheadmaster);
        }
        $res_list = $this->array_page_list_show($limit, $page, $res, 1);
        $this->returnData($res_list, '');
    }

    //数组分页方法
    public function array_page_list_show($count, $page, $array, $order)
    {
        $page = (empty($page)) ? '1' : $page; #判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; #计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $pagedata = array();
        $pagedata['limit'] = $count;
        $pagedata['countarr'] = count($array);
        $pagedata['to_pages'] = ceil(count($array) / $count);
        $pagedata['page'] = $page;
        $pagedata['data'] = array_slice($array, $start, $count);    //分隔数组
        return $pagedata;  #返回查询数据
    }

    public function edit_classes()
    {
        $where['class_id'] = input('class_id');
        $data = [
            'class_name' => input('class_name'),
            'headmaster' => input('headmaster'),
            'class_count' => input('class_count'),
            'remarks' => input('remarks'),
        ];
        $res = edit('erp2_classes', $where, $data);
        if ($res) {
            $this->returnData( '', '修改成功');
        } else {
            $this->returnError(20003, '修改失败');
        }
    }


    public function edit_classes_curs()
    {
        $where['class_id'] = input('class_id');
        $stu_id = input('stu_id');
        $arr = [];
        foreach ($stu_id as $k => $v) {
            $arr1['stu_id'] = $v;
            $arr1['class_id'] = input('class_id');
            $arr1['is_del'] = 0;
            $arr[] = $arr1;
        }
        $a = del('erp2_class_student_relations', $where);
        $b = Db::table('erp2_class_student_relations')->insertAll($arr);
        if ($b) {
            $this->returnData( '', '修改成功');
        } else {
            $this->returnError(20003, '修改失败');
        }
    }


    public function edit_curr()
    {
        $where['cls_id'] = input('class_id');
        $data['cur_id'] = input('cur_id');
        $res = edit('erp2_class_cur', $where, $data);
        if ($res) {
            $this->returnData( '', '修改成功');
        } else {
            $this->returnError(20003, '修改失败');
        }
    }


    public function del_classes()
    {
        $data['is_del'] = 1;
        $where['class_id'] = input('class_id');
        $res = edit('erp2_classes', $where, $data);
        if ($res) {
            $this->returnData( '', '修改成功');
        } else {
            $this->returnError(20003, '修改失败');
        }
    }

    public function get_teacher_list()
    {
        $res = select_find('erp2_teachers', ['org_id' => input('orgid'), 'status' => 1, 'is_del' => 0, 'is_teacher' => 1], 't_id,t_name');
        $this->return_data(1, '', '请求成功', $res);
        $this->returnData($res, '请求成功');
    }

    public function get_student_list()
    {

        $res = select_find('erp2_students', ['org_id' => input('orgid'), 'is_del' => 0], 'stu_id,truename');
        foreach ($res as $k => &$v) {
            $v['f'] = false;
        }
        $this->returnData($res, '请求成功');
    }

    public function get_fl_currlist()
    {
        $res = select_find('erp2_subjects', ['is_del' => 0, 'status' => 1, 'pid' => 0], 'sid,sname');
        foreach ($res as $k => &$v) {
            $v['currlist'] = select_find('erp2_curriculums', ['subject' => $v['sid'], 'orgid' => input('orgid'), 'is_del' => 0], 'cur_id,cur_name');
        }
//        $this->return_data(1, 0, $res);
        $this->returnData($res, '请求成功');
    }

    public function get_stu_id()
    {
        $where['class_id'] = input('cls_id');
        $res['stulist'] = selects('erp2_class_student_relations', $where);
        $res['class_id'] = input('cls_id');
//        $this->return_data(1, 0, $res);
        $this->returnData($res, '请求成功');
    }


    public function get_teach_sch()
    {
        $where['t_id'] = input('t_id');
        $where['cur_id'] = input('cur_id');
        $res = Db::table('erp2_teach_schedules')->where($where)->select();
        $this->returnData($res, '请求成功');
    }


    /*
     * 上面是刚跑路的php写的，不关我事。
     */

    /*
     * 课表接口
     */
    public function schedule()
    {


    }

}