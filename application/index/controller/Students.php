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
        /*
         * 缺少购课记录，　排课记录
         */
//        $status = input('status', '');
//        $org_id = input('org_id', '');
//        $limit = input('limit', 10);
//        $stu_name = input('stu_name', '');
//        $teacher_name = input('t_name', '');
//        $course_name = input('c_name', '');
//        $where[] = ['org_id', '=', $orgid];
//
//        if(empty($orgid))
//        {
//            $this->returnError(0, '10000', '缺少参数');
//        }
//        if (!empty($status))
//        {
//            $where[] = ['status', '=', $status];
//        }
//        if (!empty($stu_name))
//        {
//            $where[] = ['stu_name', 'like', '%' . $stu_name . '%'];
//        }
//        if (!empty($teacher_name))
//        {
//            $where[] = ['teacher_name', 'like', '%' . $teacher_name . '%'];
//        }
//        if (!empty($course_name))
//        {
//            $where[] = ['cur_name', 'like', '%' . $course_name . '%'];
//        }
//        $where[] = ['is_del', '=', 0];
//        $where[] = ['org_id', '=', $org_id];
//        $students = db('students')->field('stu_id, truename as stu_name, sex, birthday,
//                cellphone, wechat, address, remark')->where($where)->paginate($limit);
//        $this->returnData(1, '', '', $students);
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        $org_id = input('orgid/d', '');
        $stu_name = input('stu_name/s', '');
        $t_name = input('t_name/s', '');
        $cur_name = input('c_name/s', '');
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
            $already_arrange=0;//已排课程
            $remark=$v['remark'];
            $balance=StuBalance::field('gift_balance,recharge_balance')->find();
            $res_data[] = [
                'stu_id' => $stu_id,
                'stu_name' => $v['truename'],
                'mobile'    => $v['cellphone'],
                'cur_list'  => $cur_list,
                't_name'    => $t_name,
                'already_done' => $already_done,
                'already_buy'   => $already_buy,
                'surplus_lesson' => $surplus_lesson,
                'already_arrange'=>$already_arrange,
                'remarks'=>$remark,
                'balance'=>$balance['gift_balance']+$balance['recharge_balance']
            ];
        }
        $response = [
            'per_page' => $page,
            'total' => count($res_data),
            'last_page' => count($res_data) / $limit + 1,
            'data' => array_slice($res_data, ($page - 1)*$limit, $limit),
        ];
        $this->returnData($response, '请求成功');
    }

    /*
     * Modify Student Records
     */
    public function edit()
    {
        $data = input();
        if(!$this->request->isPost())
        {
            $this->returnError(0, '10007', '请用post方法提交数据');
        }

        $validate = new StuValidate();
        if(!$validate->scene('edit')->check($data)){
            $error = explode('|',$validate->getError());
            $this->returnData(0,$error[1],$error[0]);
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
        /*
         * 缺少验证余额是否清空.
         */
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
        Db::startTrans();
        try
        {
            $stu_id = Db::table('erp2_students')->insertGetId($data);
            $data  = [
                'stu_id' => $stu_id,
                'gift_balance' => 0.00,
                'recharge_balance' => 0.00,
                'create_time' => time(),
                'update_time' => time()
            ];
            // 创建用户余额表
            Db::table('erp2_stu_balance')->insert($data);
            Db::commit();
            $this->return_data(1, '', '添加学生成功', true);
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data(0, 50000, $e->getMessage(), false);
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

    /*
     * Student's balance.
     */
    public function balance(){
        $stu_id = input('stu_id', '');
        if (empty($stu_id)) {
            $this->return_data('10000', '缺少参数');
        }

    }

    /*
     * Buying Lesson
     * 用jwt是不需要传用户id的，没办法另外那个php是要这么搞，我只能迎合他。
     */
    public function buyLesson()
    {
        $orgid= \think\facade\Request::instance()->header()['orgid'];
        $data = [
            'stu_id' => input('post.stu_id/d', ''),
            'uid'   => input('post.uid/d', ''),
            't_id'    => input('post.t_id/d', ''),
            'sen_id'  => input('post.sen_id/d', ''),
            'pay_id'  => input('post.pay_id/d', ''),
            'single_price' => input('post.single_price/f', ''),
            'type'      => input('post.type/d', ''),
            'type_num'  => input('post.type_num/d', ''),
            'give_class' => input('post.give_class/d', ''),
            'class_hour' => input('post.class_hour/d', ''),
            'original_price' => input('post.original/f', ''),
            'after_price'   => input('post.after_price/f', ''),
            'real_price'    => input('post.real_price/f', ''),
            'valid_day'   => input('post.real_price/f', ''),
            'buy_time'      => input('post.buy_time/d', ''),
            'or_id'     =>$orgid
        ];
        foreach ($data as $key => $val)
        {
            if (empty($val))
            {
                $this->return_data('0', '10000', $key."不能为空");
            }
        }
        $classify = input('post.classify/d', '');   // 购课类型
        $cur_id = input('post.cur_id/d', '');  // 课程id
        $meal_id = input('post.meal_id/d', ''); // 套餐id
        $remarks = input('post.remarks/s', '');
        if (empty($classify)) {
            $this->return_data(0, '10000', '缺少classify参数');
        }

        if ($classify == 1 and empty($cur_id))  // 普通购课
        {
            $this->return_data('0', '10000', '普通购课cur_id不能为空');
        }
        if ($classify == 2 and empty($meal_id)) //套餐购课
        {
            $this->return_data('0', '10000', '普通购课meal_id不能为空');
        }
        $data['cur_id'] = $cur_id;
        $data['meal_id'] = $meal_id;
        $data['remarks'] = $remarks;
        $data['manager'] = $data['uid'];
        unset($data['uid']);
        $data['buy_time'] = time();
        $data['create_time'] = time();
        Db::startTrans();
        try
        {

            Db::table('erp2_purchase_lessons')->insert($data);
            Db::commit();
            $this->return_data('1', '', '购课成功', true);
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data('0', '', '购课失败', false);
        }
    }

    /*
     * 学生充值
     */
    public function recharge()
    {
        $recharge_amount = input('recharge/f', 0.00);
        $give_amount = input('give/f', 0.00);
        $remark = input('remark/s', '');
        $stu_id = input('stu_id', '');
        if (empty($stu_id))
        {
            $this->return_data('0', '10000', '缺少stu_id', false);
        }
        Db::startTrans();
        try{
            $data = Db::where(['stu_id' => $stu_id])->find();
            if (empty($data))
            {   // 未创建钱包的用户
                $this->return_data('0', '50000', '系统错误');
            }
            $data['gift_balance'] += $give_amount;
            $data['recharge balance'] += $recharge_amount;
            Db::table('erp2_stu_balance')->where(['stu_id'=>$stu_id])->update($data);
            //　充值记录，　账号明细需求是有个充值余额和赠送余额的概念，保持一至。
            $recharge_log = [
                'stu_id' => $stu_id,
                'recharge_amount' => $recharge_amount,
                'give_amount' => $give_amount,
                'recharge_balance' => $data['gift_balance'],
                'give_balance' => $data['give_balance'],
                'remark' => $remark,
                'is_del' => 0,
                'create_time' => time(),
                'update_time' => time()
            ];
            Db::table('ero2_stu_recharges')->insert($recharge_log);
            Db::commit();
            $this->return_data('0', '', '充值成功', true);
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data('0', '50000', '服务器错误');
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