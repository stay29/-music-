<?php

namespace app\index\controller;

use app\index\model\MealCurRelations;
use app\index\model\Purchase_Lessons;
use app\index\model\Schedule;
use MongoDB\BSON\Decimal128;
use function PHPSTORM_META\type;
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
        $this->auth_get_token();
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

//        var_dump($cur_name);
//        if (!empty($cur_name))
//        {
//            $cur_id = db('curriculums')->where('cur_name', 'like', '%'.$cur_name.'%')->value('cur_id');
//            $cur_id = $cur_id ? $cur_id : -1;
//            $sql = "SELECT * FROM erp2_students WHERE stu_id IN (SELECT stu_id FROM erp2_stu_cur WHERE cur_id={$cur_id});";
//            array_push($data, Db::query($sql));
//        }
        $data = $tb->select();
        $res_data = [];
        foreach ($data as $k=>$v)
        {
            $stu_id = $v['stu_id'];
            $cur_sql = "SELECT cur_name FROM erp2_curriculums WHERE cur_id IN (SELECT cur_id FROM erp2_teach_schedules WHERE stu_id={$stu_id});";
            $cur_list = Db::query($cur_sql);

            if(!empty($cur_name)){
//                var_dump($cur_name);
                $is_over=false;
                foreach ($cur_list as $cur){

                    if(strpos($cur['cur_name'],$cur_name)!==FALSE){
                        $is_over=true;
                        break;
                    }

                }
                if(!$is_over){
                    continue;
                }
            }
//           var_dump(!empty($t_name));

                $res=\app\index\model\Teacher::field('a.t_name')
                    ->alias('a')
                    ->where('a.org_id',$org_id)
                    ->where('b.org_id',$org_id)
                    ->join('erp2_teach_schedules b','b.t_id=a.t_id')
                    ->where('FIND_IN_SET(:stu_id,stu_id)', ['stu_id' => $stu_id])
                    ->distinct(true)
                    ->select();
            if(!empty($t_name)){
//                var_dump($res);
                $is_over=false;
                foreach ($res as $value){
//                    var_dump(strpos($value['t_name'],$t_name));
                  if(strpos($value['t_name'],$t_name)!==FALSE){
                      $is_over=true;
                      break;
                  }
                }
                if(!$is_over){
                    continue;
                }


            }
            $t_name_list = empty($res) ? '' : $res;
            // 已购课程
            $already_buy = db('teach_schedules')->where(['status'=>1, 'stu_id'=>$stu_id])->count('*');
            // 已上课程
            $already_done = db('teach_schedules')->where(['status'=>2, 'stu_id'=>$stu_id])->count('*');

            $surplus_lesson = 0;    // 剩余课程写死为0
            $already_arrange=0;//已排课程
            $remark=$v['remark'];
            $balance=StuBalance::field('gift_balance,recharge_balance')->where('stu_id',$stu_id)->find();
            $res_data[] = [
                'stu_id' => $stu_id,
                'stu_name' => $v['truename'],
                'mobile'    => $v['cellphone'],
                'cur_list'  => $cur_list,
                't_name'    => $t_name_list,
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
        $this->auth_get_token();
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
        $this->auth_get_token();
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
        $this->auth_get_token();
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
        $this->auth_get_token();
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
        $this->auth_get_token();
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
        $this->auth_get_token();
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
        $this->auth_get_token();
        $stu_id = input('stu_id', '');
        if (empty($stu_id)) {
            $this->return_data('10000', '缺少参数');
        }

    }

    /*
     * Buying Lesson
     */
    public function buyLesson()
    {
        $this->auth_get_token();
        $orgid= \think\facade\Request::instance()->header()['orgid'];
        $real_price=input('post.real_price/f', '');
        $classify = input('classify/d', '');   // 购课类型
        $remarks = input('post.remarks/s', '');
        $pay_id= input('post.pay_id/d', '');
        $type=input('post.type/d', '');
        $stu_id=input('stu_id/d', '');
        if (empty($classify)) {
            $this->return_data(0, '10000', '缺少classify参数');
        }


        if ($classify == 1)  // 普通购课
        {
            $cur_id = input('post.cur_id/d', '');  // 课程id
//            //如果之前学生有购过这个课，只需要更新记录不需要创建
//            $pl=Purchase_Lessons::where(['cur_id'=>$cur_id,'stu_id'=>$stu_id])->find();
//                if($pl!=null){
//                    Purchase_Lessons::where(['cur_id'=>$cur_id,'stu_id'=>$stu_id])->update([
//                        'bug_time'=>time(),
//                        ''
//                    ]);
//                }
            $give_class=input('post.give_class/d', 0);
            $class_hour=input('post.class_hour/d', '');

            $totol_ch=$class_hour+$give_class;
            $data_record=[
                'stu_id' =>$stu_id ,
                'pay_id'  =>$pay_id,
                'type'      => $type,
                'type_num'  => input('post.type_num/d', ''),
                'original_price' => input('post.original/f', ''),
                'disc_price'   => input('post.disc_price/f', ''),
                'real_price'    => $real_price,
                'valid_day'   => input('post.real_price/f', ''),
                'buy_time'      => input('post.buy_time/d', ''),
                'give_class' => $give_class,
                'or_id'     =>$orgid,
                'classify'=>$classify
            ]  ;
            $data = [
                'stu_id' =>$stu_id ,
                'uid'   => input('post.uid/d', ''),
                't_id'    => input('post.t_id/d', ''),
                'sen_id'  => input('post.sen_id/d', ''),
                'single_price' => $real_price/$totol_ch,
                'class_hour' => $totol_ch,
                'surplus_hour'=>$totol_ch,
                'or_id'     =>$orgid,
            ];
            foreach ($data as $key => $val)
            {
                if ($val='')
                {
                    if($key=='type_num'&&$data['type']==1){

                    }else
                        $this->return_data('0', '10000', $key."不能为空");
                }
            }
            if(empty($cur_id))
            $this->return_data('0', '10000', '普通购课cur_id不能为空');
            $data['cur_id'] = $cur_id;
            $data['remarks'] = $remarks;
            $data['manager'] = $data['uid'];
            unset($data['uid']);
            $data['buy_time'] = time();
            $data['create_time'] = time();
            $data_record['name']=input('cur_name');
            Db::startTrans();
            try
            {
               $record=  Db::table('erp2_purchase_lessons_record')->insertGetId($data_record);
                $data['r_id']=$record;
                Db::table('erp2_purchase_lessons')->insert($data);
                //处理账户钱

                $this->money_count($stu_id, $real_price, $pay_id);
                Db::commit();
                $this->return_data('1', '', '购课成功', true);
            }catch (Exception $e)
            {
                Db::rollback();
                $this->return_data('0', '', $e->getMessage(), false);
            }
        }
        if ($classify == 2) //套餐购课
        {
            $meal_id = input('post.meal_id/d', ''); // 套餐id
            if(empty($meal_id))
            $this->return_data('0', '10000', '普通购课meal_id不能为空');
           $meals= \app\index\model\Meals::where('meal_id',$meal_id)->find();
            $data_record=[
                'stu_id' =>$stu_id ,
                'pay_id'  => $pay_id,
                'type'      => $type,
                'type_num'  => input('post.type_num/d', ''),
                'original_price' => input('post.original/f', ''),
                'disc_price'   => input('post.disc_price/f', ''),
                'real_price'    => $real_price,
                'valid_day'   => input('post.real_price/f', ''),
                'buy_time'      => input('post.buy_time/d', ''),
                'or_id'     =>$orgid,
                'classify'=>$classify
            ]  ;
            $data_record['name']=$meals['meal_name'];
            Db::startTrans();
            try
            {
            $record=  Db::table('erp2_purchase_lessons_record')->insertGetId($data_record);
           $meals_cur_id= explode('/',$meals['meals_cur']);
           $cur_objs= MealCurRelations::where('meal_cur_id','in',$meals_cur_id)->select();

            foreach ($cur_objs as $cur_obj){
//                var_dump( $cur_obj['cur_id']);
                $class_hour=$cur_obj['cur_num'];
                $real_price=$cur_obj['actual_price'];
                $totol_ch=$class_hour;
//                var_dump($data['cur_id']);
                var_dump($stu_id);
                $data = [
                    'r_id'=>$record,
                    'stu_id' => $stu_id,
//                    'uid'   => input('post.uid/d', ''),
                    'single_price' => $real_price/$totol_ch,
                    'class_hour' => $totol_ch,
                    'surplus_hour'=>$totol_ch,
                    'or_id'     =>$orgid
                ];
                $data['cur_id'] = $cur_obj['cur_id'];
                $data['meal_id'] = $meal_id;
                $data['remarks'] = $remarks;
                $data['manager'] = input('post.uid/d', '');
//                unset($data['uid']);
                $data['buy_time'] = time();
                $data['create_time'] = time();
                    Db::table('erp2_purchase_lessons')->insert($data);

            }
                //处理账户钱
                $this->money_count($stu_id, $real_price, $pay_id);
                Db::commit();
                $this->return_data('1', '', '购课成功', true);
            }catch (Exception $e)
            {
                Db::rollback();
                $this->return_data('0', '', $e->getMessage(), false);
            }
        }

    }

    /*
     * 学生充值
     */
    public function recharge()
    {
        $this->auth_get_token();
        $recharge_amount = input('recharge/f', 0.00);
        $give_amount = input('give/f', 0.00);
//        var_dump($give_amount);
        $remark = input('remark/s', '');
        $stu_id = input('stu_id', '');
        if (empty($stu_id))
        {
            $this->return_data('0', '10000', '缺少stu_id', false);
        }
        Db::startTrans();
        try{
            $data = Db::table('erp2_stu_balance')->where(['stu_id' => $stu_id])->find();

            if (empty($data))
            {   // 未创建钱包的用户
            Db::table('erp2_stu_balance')->insert(['stu_id'=>$stu_id,
                    'create_time'=>time(),
                    'update_time'=>time()
                    ]);
                $data = Db::table('erp2_stu_balance')->where(['stu_id' => $stu_id])->find();
//                var_dump($data['gift_balance']);
            }
            $data['gift_balance'] += $give_amount;
            $data['recharge_balance'] += $recharge_amount;
            $data['total_recharge']+=$recharge_amount;
            $data['total_gift']+=$give_amount;

            Db::table('erp2_stu_balance')->where(['stu_id'=>$stu_id])->update($data);
            //　充值记录，　账号明细需求是有个充值余额和赠送余额的概念，保持一至。
            $recharge_log = [
                'pid' => $stu_id,
                'amount' => $recharge_amount,
                'presenter' => $give_amount,
                'remark' => $remark,
                'balance'=>$data['gift_balance']+$data['recharge_balance'],
                'pay_id'=>input('pay_id'),
                'type'=>'充值',
//                'is_del' => 0,
                'create_time' => time(),
                'update_time' => time()
            ];
            Db::table('erp2_stu_balance_log')->insert($recharge_log);
            Db::commit();
            $this->return_data('1', '', '充值成功', '');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data('0', '50000', $e->getMessage());
        }
    }

    /**
     * 学生所上课程列表
     */
    public function bug_schedule_list(){
        $stu_id=input('stu_id');
       $data=Purchase_Lessons::field('b.t_name,c.cur_name,a.single_price,a.give_class,a.surplus_hour,a.class_hour')
           ->alias('a')
       ->where('a.stu_id',$stu_id)
           ->join('erp2_teachers b','a.t_id=b.t_id')
           ->join('erp2_curriculums c','c.cur_id=a.cur_id')
           ->select();
       $this->returnData($data,"");
    }
   /**
    * 学生购课课程记录
    */
    public function bug_schedule_list_record(){
        $stu_id=input('stu_id');
        $data=Db::table('erp2_purchase_lessons_record')->field('a.name,a.type,a.type_num,a.classify,b.payment_method,a.real_price,a.remarks,a.r_id,a.create_time')
            ->alias('a')
            ->where('a.stu_id',$stu_id)

            ->join('erp2_payments b','b.pay_id=a.pay_id')
            ->select();
        foreach ($data as $k=>$value){
//            var_dump($value['r_id']);
        $data[$k]['cur'] =Purchase_Lessons::field('single_price,give_class,class_hour,c.cur_name')
            ->alias('a')
            ->where('r_id',$value['r_id'])
            ->join('erp2_curriculums c','c.cur_id=a.cur_id')
            ->select();
        }
        $this->returnData($data,"");
    }

    /**
     * @param $stu_id
     * @param $real_price
     * @param $pay_id
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function money_count($stu_id, $real_price, $pay_id)
    {
        $account = Db::table('erp2_stu_balance')->where('stu_id', $stu_id)->find();
//                 var_dump($account);
        $money = [
            'total_buylesson' => $account['total_buylesson'] + $real_price,
        ];
        $log = ['type' => '购课',
            'pay_id' => $pay_id,
            'pid' => $stu_id,
            'create_time' => time(),
            'update_time' => time()
        ];
//                switch ($type){
//                    case  1: //不使用优惠
//                        break;
//                    case 2:// 折扣
//                        break;
//                    case 3://优惠金额
//                        break;
//                }
//                var_dump($pay_id);
        if ($pay_id == 6) {
            if ($real_price < $account['recharge_balance']) {
                $money['recharge_balance'] = $account['recharge_balance'] - $real_price;
                $log['amount'] = $real_price;
            } else if ($real_price < $account['recharge_balance'] + $account['gift_balance']) {
                $money['recharge_balance'] = 0;
                $money['gift_balance'] = $account['gift_balance'] + $account['recharge_balance'] - $real_price;
                $log['amount'] = -$account['recharge_balance'];
                $log['presenter'] = $account['recharge_balance'] - $real_price;
            } else {
                $money['recharge_balance'] = $account['gift_balance'] + $account['recharge_balance'] - $real_price;
                $money['gift_balance'] = 0;
                $log['amount'] = -$real_price;
                $log['presenter'] = -$money['gift_balance'];
            }
//                    var_dump($money['recharge_balance']);
            $log['balance'] = $account['gift_balance'] + $account['recharge_balance'] - $real_price;
        } else {
            $log['amount'] = -$real_price;
        }
        StuBalance::where('stu_id', $stu_id)->update($money);
        Db::table('erp2_stu_balance_log')
            ->insert($log);
    }

    /**
     * 学生账户明细
     */
    public function  account_details(){
        $stu_id=input('stu_id');
     $data=Db::name('stu_balance')->where('stu_id',$stu_id)
         ->select();
     $data[0]['details']=Db::field('c.truename,a.amount,a.presenter,b.payment_method,a.balance,a.remark,a.create_time,a.type')
         ->name('stu_balance_log')
         ->alias('a')
         ->join('erp2_payments b','a.pay_id=b.pay_id')
         ->join('erp2_students c',['c.stu_id'=>$stu_id])
         ->where('pid',$stu_id)
         ->order('a.create_time desc')
         ->select();
     $this->returnData($data,'');
    }
    /**
     * 学生其他购买
     */
     public function other_buy(){
         $stu_id=input('stu_id');
         //销售员的记录
            $data= Db::field("a.create_time,b.payment_method,a.sum_payable,c.goods_name,a.sale_code,a.sale_num,a.remark,d.sm_name as name,a.sman_type")
                 ->name('goods_sale_log')
                 ->alias("a")
                 ->where(['a.sale_obj_id'=>$stu_id,"a.sale_obj_type"=>1,'a.sman_type'=>1])
                 ->join('erp2_payments b','a.pay_id=b.pay_id')
                ->join('erp2_goods_detail c','c.goods_id=a.goods_id')
                ->join('erp2_salesmans d','d.sm_id=a.sale_obj_id')
                 ->select();
            //老师的记录
         $data1=Db::field("a.create_time,b.payment_method,a.sum_payable,c.goods_name,a.sale_code,a.sale_num,a.remark,d.t_name as name,a.sman_type")
             ->name('goods_sale_log')
             ->alias("a")
             ->where(['a.sale_obj_id'=>$stu_id,"a.sale_obj_type"=>1,'a.sman_type'=>2])
             ->join('erp2_payments b','a.pay_id=b.pay_id')
             ->join('erp2_goods_detail c','c.goods_id=a.goods_id')
             ->join('erp2_teachers d','d.t_id=a.sale_obj_id')
             ->select();
            $this->returnData(array_merge($data,$data1));
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