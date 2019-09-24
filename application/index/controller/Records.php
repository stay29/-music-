<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-27
 * Time: 下午4:53
 */

namespace app\index\controller;

/*
 * 销售记录，入库记录， 租赁记录， 销售统计相关接口
 */


use think\Db;
use Think\Exception;
use think\facade\Request;

class Records extends BaseController
{
    /*
     * 销售记录列表
     */
    public function sale_index()
    {
        $this->auth_get_token();
        $goods_name = input('goods_name/s', '');
        $org_id = input('orgid/d', '');
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        try
        {
            if (is_empty($org_id))
            {
                $this->returnError(10000, $org_id);
            }
            $db = db('goods_detail')->where('org_id', '=', $org_id);
            if(!empty($goods_name))
            {
                $db->where('goods_name', 'like', '%' . $goods_name . '%');
            }

            $goods_list = $db->field('goods_id, goods_name, cate_id')->order('create_time DESC')->select();
//            $response = [];
            $data = [];
            foreach ($goods_list as $goods)
            {
                $goods_id = $goods['goods_id'];
                $cate_name = db('goods_cate')->where('cate_id', '=', $goods['cate_id'])->value('cate_name');
                $sale_logs = db('goods_sale_log')->
                field('sale_id, sale_num, sale_code, sman_type, 
                sman_id, sale_obj_type, sale_obj_id, single_price, sum_payable,sale_time,
                pay_amount, pay_id, remark, manager')->where('goods_id', '=', $goods_id)->order('create_time DESC')
                ->select();
                foreach ($sale_logs as $log)
                {
                    $sman_name = '';
                    $sale_obj_name = '';
                    if ($log['sman_type'] == 1) // 销售员
                    {
                        $sman_name = db('salesmans')->where('sm_id', '=', $log['sman_id'])->value('sm_name');
                    }elseif ($log['sman_type'] == 2)  // 老师
                    {
                        $sman_name = db('teachers')->where('t_id','=', $log['sman_id'])->value('t_name');
                    }
                    if ($log['sale_obj_type'] == 1)
                    {
                        $sale_obj_name = db('students')->where('stu_id',
                            '=', $log['sale_obj_id'])->value('truename');
                    }else{
                        $sale_obj_name = '其他';
                    }

                    $manager = db('users')->where('uid', '=', $log['manager'])->value('nickname');
                    $manager = $manager ? $manager : '管理员';
                    $pay_type = db('payments')->where('pay_id', '=', $log['pay_id'])
                        ->value('payment_method');
                    $data[] = [
                        'goods_name' => $goods['goods_name'],
                        'cate_name'  => $cate_name,
                        'sale_id'  => $log['sale_id'],
                        'sale_num'  => $log['sale_num'],
                        'sale_code' => $log['sale_code'],
                        'sman_name' => $sman_name,
                        'sman_type' => $log['sman_type'],
                        'sman_id'   => $log['sman_id'],
                        'sale_time' => $log['sale_time'],
                        'sale_obj_type' => $log['sale_obj_type'],
                        'sale_obj_id' => $log['sale_obj_id'],
                        'sale_obj_name' => $sale_obj_name,
                        'manager' => $manager,
                        'pay_type' => $pay_type,
                        'pay_id' => $log['pay_id'],
                        'single_price' => $log['single_price'],
                        'sum_payable' => $log['sum_payable'],
                        'pay_amount' => $log['pay_amount'],
                        'remark' => $log['remark'],
                    ];
                }

            }
            $response = [
                'per_page' => $page,
                'last_page' => intval(count($data) / $limit) + 1,
                'total' => count($data),
                'data' => array_slice($data, ($page - 1) * $limit, $limit)
            ];
            $this->returnData($response, '');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统错误' . $e->getMessage());
        }
    }

    /*
      * 销售记录删除
       */
    public function sale_del()
    {
        $this->auth_get_token();
        $sale_id = input('sale_id/d', '');
        if (is_empty($sale_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        try{
            db('goods_sale_log')->where('sale_id', '=', $sale_id)->delete();
            $this->returnData('', '删除成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统错误');
        }
    }

    /*
     * 修改销售记录
     */
    public function sale_edit()
    {
        $this->auth_get_token();
        $data = [
            'sale_id' => input('sale_id/d', ''),
            'sman_type' => input('sman_type/d', ''),
            'sale_num' => input('sale_num/d', ''),
            'sman_id' => input('sman_id/d', ''),
            'sale_obj_type' => input('sale_obj_type/d', ''),
            'sale_obj_id' => input('sale_obj_id/d', ''),
            'single_price' => input('single_price/f', ''),
            'sum_payable' => input('sum_payable/f', ''),
            'pay_amount' => input('pay_amount/f', ''),
            'sale_time' => input('sale_time/d', ''),
            'pay_id' => input('pay_id/d', ''),
            'remark' => input('remarks/s', ''),
            'update_time' => time(),
        ];
        $validate = new \app\index\validate\SaleLog();
        if (!$validate->check($data)){
            $errors = explode('|', $validate->getError());
            $this->returnError($errors[1], $errors[0]);
        }
        try
        {
            $sale_id = $data['sale_id'];
            unset($data['sale_id']);
            db('goods_sale_log')->where('sale_id', '=', $sale_id)->update($data);
            $this->returnData(true, '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统出错' . $e->getMessage());
        }

    }

    /*
     * 租凭记录列表
     */
    public function rental_index()
    {
        /*
            d)	总押金：当前筛选条件下的租赁记录信息中押金之和
            e)	总预收租金：当前筛选条件下的租赁记录信息中预收租金之和
            f)	已收租金：当前筛选条件下的租赁记录信息中已实收租金之和
         */
        $this->auth_get_token();
        $status_arr = [1=>'在租', 2=>'超期', 3=>'已归还']; // 租凭状态对应状态
        $rent_type_arr = [0=>'', 1=>'日', 2=>'月', 3=>'年'];       // 租凭方式对应含义
        $rent_type_amount_arr = [0=>'', 1=>'rent_amount_day', 2=>'rent_amount_mon', 3=>'rent_amount_year'];
        $org_id = input('orgid/d', '');
        $start_time = input('start_time/d', '');
        $end_time = input('end_time/d', '');
        $key = input('key/s', '');  // 租客姓名/商品名称
        $status = input('status/d', 1); // 1 全部， 2在租， 3超期， 4已归还。
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        if (empty($org_id))
        {
            $this->returnError(10000, '缺少机构ID');
        }
        try{
            $rent_obj_id = [];  # 租赁对象ID
            $goods_id = []; # 商品ID

            # 租客名称或者商品民粹
            $gsdb =  db('goods_detail')->where('org_id', '=', $org_id);
            $studb = db('students')->where('org_id', '=', $org_id);
            if ($key)
            {
                $studb->where('truename', 'like', '%' . $key . '%');

                $gsdb->where('goods_name', 'like', '%' . $key . '%');

            }
            $rent_obj_id = $studb->column('stu_id');
            $goods_id = $gsdb->column('goods_id');
            # 租赁记录表
            $table = db('goods_rental_log')->alias('grent')->field("grent.*, gd.goods_name, stu.truename, gd.rent_amount_day, gd.rent_amount_mon, gd.rent_amount_year");

            if ($goods_id) {$table->where('grent.goods_id', 'in', $goods_id);}
            if ($rent_obj_id) {$table->where('grent.rent_obj_id', 'in', $rent_obj_id);}
            # 前端参数１是全部，数据库存储状态１是在租
            if ($status and $status!=1) {$table->where('grent.status', '=', $status-1);}
            if (!empty($start_time) and !empty($end_time)) {$table = $table->whereBetweenTime('grent.create_time',  $start_time,  $end_time);}
            $table->group('grent.goods_id, grent.rent_obj_id');
            $table->order('grent.create_time DESC');
            $total_margin = $table->sum('rent_margin'); // 总押金
            $total_amount = $table->sum('rent_amount');  // 总租金
            $total_prepaid_rent = $table->sum('prepaid_rent');  // 总预收租金
               
            $rent_logs = $table->leftJoin('erp2_goods_detail gd', 'gd.goods_id=grent.goods_id')
                                ->leftJoin('erp2_students stu', 'stu.stu_id = grent.rent_obj_id')
                                ->paginate($limit, false, ['page' => $page])
                                ->each(function($log, $lk) use ($rent_type_amount_arr, $status_arr){
                                    unset($log['remarks']);
                                    $rent_obj_name = '其他';
                                    if ($log['rent_obj_type'] == 1){
                                        $rent_obj_name = $log['truename'];
                                    }
                                    //每日/月/年租金
                                    $rent_type_money = $log[$rent_type_amount_arr[$log['rent_type']]];
                                    $log['rent_type_money'] = $rent_type_money;
                                    
                                    if (time() > $log['end_time'] and $log['status'] != 3) // 超时未归还
                                    {
                                        $log['status'] = 2;
                                    }
                                    $log['status_text'] = $status_arr[$log['status']];
                                    return $log;
                                });
            $data = [
                'total_margin' => $total_margin,
                'total_amount' => $total_amount,
                'total_prepaid_rent' => $total_prepaid_rent,
                'records' => $rent_logs->items()
                ];
            $response = [
                'last_page' => $rent_logs->lastPage(),
                'per_page' => $rent_logs->listRows(),
                'total' => $rent_logs->total(),
                'data' => $data
            ];
//            dump($data);die;
//            $logs = $table->order('create_time DESC')->select();   //
//            foreach ($logs as $log) {
//                $g_id = $log['goods_id'];
//                $rent_id = $log['rent_id'];
//                $rent_obj_type = $log['rent_obj_type'];
//                $rent_obj_id = $log['rent_obj_id'];
//                $goods_name = db('goods_detail')->where('goods_id', '=', $g_id)->value('goods_name');
//                $rent_obj_name = '其他';
//                if ($rent_obj_type == 1)  // 1是学生， 2是其他对象
//                {
//                    $rent_obj_name = db('students')->where('stu_id', '=', $rent_obj_id)
//                        ->value('truename');
//                }
//                $rent_num = $log['rent_num'];
//                $start_time = $log['start_time'];
//                $end_time = $log['end_time'];
//                $rent_type = $rent_type_arr[$log['rent_type']]; // 租借方式
//                $rent_type_money = db('goods_detail')->      // 租借方式
//                                        where('goods_id', '=', $g_id)->
//                                        value($rent_type_amount_arr[$log['rent_type']]);
//                $rent_amount = $log['rent_amount'];  // 租金金额
//                $prepaid_rent = $log['prepaid_rent']; // 预付租金
//                $status = $log['status'];
//                if (time() > $log['end_time'] and $status != 3) // 超时未归还
//                {
//                    $status = 2;
//                }
//                $status_text = $status_arr[$status];    // 租凭状态对应文字
//                $remarks = $log['remarks'];
//                $data['records'][] = [
//                    'rent_id' => $rent_id,  // 租借记录id
//                    'goods_name' => $goods_name,
//                    'rent_margin' => $log['rent_margin'],
//                    'rent_code' => $log['rent_code'], // 租借单号
//                    'rent_obj_name' => $rent_obj_name, // 租借对象姓名
//                    'rent_obj_id'   => $rent_obj_id,    // 租借对象id
//                    'rent_obj_type' => $rent_obj_type,  // 租借对象类型1学生, 其他
//                    'rent_num'  => $rent_num,   // 租借数量
//                    'start_time' => $start_time,    // 租借开始时间
//                    'end_time'  => $end_time,   // 租借结束时间
//                    'rent_type' => $rent_type,  // 租借类型
//                    'rent_type_money' => $rent_type_money,  // 租借类型对应租金
//                    'rent_amount' => $rent_amount,  // 租金
//                    'prepaid_rent' => $prepaid_rent,    // 预付租金
//                    'status' => $status,    // 租借状态
//                    'status_text' => $status_text,
//                    'remarks' => $remarks,
//                    'pay_id' => $log['pay_id'], // 支付方式
//                ];
//            }
//            $response = [
//                'last_page' => intval(count($data['records']) / $limit) + 1,
//                'per_page' => $limit,
//                'total' => count($data['records']),
//                'data' => $data
//            ];
//            $response['data']['records'] = array_slice($data['records'], ($page-1)*$limit, $limit);
            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统错误: ' . $e->getMessage());
        }
    }

    /*
     * 租赁记录详情表
     */
    public function rental_detail()
    {
        $this->auth_get_token();
        $goods_id = input('goods_id/d', '');
        $start_time = input('start_time/d', '');
        $end_time = input('end_time/d', '');
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        if (is_empty($goods_id)) {
            $this->returnData(10000, '缺少参数');
        }
        try
        {
            $ren_db = db('goods_rental_log')->alias('grent')->field('grent.*, gd.goods_name, stu.truename');
            $ren_db->where('grent.goods_id', '=', $goods_id);
            $rent_logs = $ren_db->leftJoin('erp2_goods_detail gd', 'grent.goods_id=gd.goods_id')
                    ->leftJoin('erp2_students stu', 'grent.rent_obj_id=stu.stu_id')
                    ->paginate($limit, false, ['page' => $page])
                    ->each(function($log, $lk) {
                        unset($log['remark']);
                        $log['rent_obj_name'] = '其他';
                        if ($log['rent_obj_type'] == 1){
                            $log['rent_obj_name'] = $log['truename'];
                        }
                        // 每天费用
                        $rent_amount_day = $this->get_amount_of_day($log['rent_type'], $log['goods_id']);
                        $interval_time = timediff($log['start_time'], time());
                        $pay_amount = $rent_amount_day * $interval_time['day']; // 实际租金
                        $refund_amount = $log['prepaid_rent'] + $log['rent_margin'] - $pay_amount; 
                        $log['refund_amount'] = $refund_amount;
                        return $log;
                    });
            $response = [
                'last_page' => $rent_logs->lastPage(),
                'per_page' => $rent_logs->listRows(),
                'total' => $rent_logs->total(),
                'data' => $rent_logs->items()
            ];
//            $goods_name = db('goods_detail')->
//                where('goods_id', '=', $log['goods_id'])->value('goods_name');
//            $rent_obj_name = '其他';
//            if ($log['rent_obj_type'] == 1)
//            {
//                $rent_obj_name = db('students', '=', $log['rent_obj_id'])->value('truename');
//            }
//            // 每天费用
//            $rent_amount_day = $this->get_amount_of_day($log['rent_type'], $log['goods_id']);
//            $interval_time = timediff($log['start_time'], time());
//            $pay_amount = $rent_amount_day * $interval_time['day']; // 实际租金
//            $refund_amount = $log['prepaid_rent'] + $log['rent_margin'] - $pay_amount;
//            $data = [
//                'rent_id' => $log['rent_id'],
//                'goods_name' => $goods_name, // 商品名称
//                'rent_num' => $log['rent_num'], // 租借数量
//                'rent_type' => $log['rent_type'], // 租借类型
//                'rent_margin' => $log['rent_margin'], // 租凭押金
//                'rent_obj_name' => $rent_obj_name,
//                'prepaid_rent' => $log['prepaid_rent'], // 预付租金
//                'start_time'    => $log['start_time'],
//                'end_time'  => $log['end_time'],    // 结束时间
//                'remarks'   => $log['remarks'],     // 备注
//                'pay_id'    => $log['pay_id'],      // 支付方式id
//                'pay_amount' => $pay_amount,  // 实际付款
//                'refund_amount' => $refund_amount, // 实际退款
//            ];
            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统错误' . $e->getMessage());
        }
    }


    /*
     * 租赁归还
     */
    public function rental_recover()
    {
        $this->auth_get_token();
        $pay_amount = input('pay_amount/f', ''); // 实际租金
        $refund_amount = input('refund_amount/f', ''); // 实退金额
        $pay_id = input('pay_id/d', '');    // 支付方式
        $return_time = input('return_time/d', '');
        $rent_id = input('rent_id/d', '');

        if (is_empty($pay_amount, $refund_amount, $pay_id, $return_time, $rent_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        Db::startTrans();
        try
        {
            Db::name('goods_rental_log')->where('rent_id', '=', $rent_id)->update(['status' => 1]);
            $data = [
                'pay_amount' => $pay_amount,
                'refund_amount' => $refund_amount,
                'pay_id'    => $pay_id,
                'return_time'   => $return_time,
                'rent_id'   => $rent_id
            ];
            Db::name('goods_refund_log')->insert($data);
            $this->returnData(true, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统错误' . $e->getMessage());
        }
    }

    /*
     * 续租页面数据
     */
    public function rerent_detail()
    {
        /*
         * 租客姓名、商品名称、租赁数量、计费方式、已交押金、已交租金、开始时间、结束时间、到期租金、租赁备注等，均只显示，不可修改，
         */
        $this->auth_get_token();
        $rent_id = input('rent_id/d',  '缺少参数');

        if (is_empty($rent_id))
        {
            $this->returnError(10000, '缺少参数');
        }
    }

    /*
     * 续租提交
     */
    public function rerent_edit()
    {
        $this->auth_get_token();
        $rent_id = input('rent_id/d', '');
        if (is_empty($rent_id))
        {
            $this->returnError(10000, '缺少参数');
        }
    }


    /*
     * 租借记录修改
     */
    public function rental_edit()
    {
        $this->auth_get_token();
        $rent_id = input('rent_id/d', ''); // 租借记录id
        $rent_margin = input('rent_margin/f', '');  // 租金押金
        $prepaid_rent = input('prepaid_rent/f', ''); // 预付租金
        $end_time = input('end_time/d', '');
        $remarks = input('remark');
        if (is_empty($rent_id, $rent_margin, $prepaid_rent))
        {
            $this->returnError(10000, '缺少参数');
        }
        try
        {
            $data = [
                'rent_margin' => $rent_margin,
                'prepaid_rent' => $prepaid_rent,
                'end_time' => $end_time,
                'remark' => $remarks
            ];
            db('goods_rental_log')->where('rent_id', '=', $rent_id)->update($data);
            $this->returnData('', '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统出错' . $e->getMessage());
        }
    }

    /*
     * 删除租凭记录
     */
    public function rental_del()
    {
        $this->auth_get_token();
        $rent_id = input('rent_id/d', '');
        if (is_empty($rent_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        try
        {
            db('goods_rental_log')->where('rent_id', '=', $rent_id)->delete();
            $this->returnData('', '删除成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '删除失败');
        }
    }

    /*
     * 计算每天的费用
     */
    private function get_amount_of_day($rent_type, $g_id)
    {
        $this->auth_get_token();
        if ($rent_type == 1)
        {
            return db('goods_detail')->where('goods_id', '=', $g_id)->value('rent_amount_day');
        }elseif($rent_type == 2)
        {
            return db('goods_detail')->where('goods_id', '=', $g_id)->value('rent_amount_mon') / 30;
        }elseif ($rent_type == 3)
        {
            return db('goods_detail')->where('goods_id', '=', $g_id)->value('rent_amount_year') / 365;
        }
    }

    /*
     * 入库记录首页
     */
    public function storage_index()
    {
        $this->auth_get_token();
        $goods_name = input('goods_name/s', '');
        $org_id = input('orgid/d', '');
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        if (is_empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }

        $where = [['org_id','=', $org_id]];
        if($goods_name != null){
            $where[] = ['goods_name', 'like', '%' . $goods_name . '%'];
        }
        $goods_list = db('goods_detail')->where($where)
            ->order('create_time DESC')->column('goods_id');
        //$gids = array_column($goods_list, 'goods_id');
        try
        {
            $sto_logs =  db('goods_storage')->alias('gs')->field('gs.sto_id, gs.sto_num, gs.sto_single_price, gs.sto_code, 
                           gs.entry_time, gs.manager, gs.remark, u.nickname, ed.goods_name')->where('gs.goods_id', 'in', $goods_list)
                ->leftJoin('erp2_users u', 'gs.manager=u.uid')
                ->leftJoin('erp2_goods_detail ed', 'ed.goods_id=gs.goods_id')
                ->paginate($limit, false, ['page' => $page])
                ->each(function($log, $lk){
                    $log['sto_total_money'] = $log['sto_num'] * $log['sto_single_price'];
                    return $log;
                });
            $response = [
                'total' => $sto_logs->total(),
                'per_page' => $sto_logs->listRows(),
                'last_page' => $sto_logs->lastPage(),
                'data' => $sto_logs->items()
            ];

            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '服务器错误');
        }
    }

    /*
     * 入库记录修改
     */
    public function storage_edit()
    {
        $this->auth_get_token();
        $sto_id = input('sto_id/d', '');
        $sto_num = input('sto_num/d', '');
        $sto_price = input('sto_price/f', '');
        $remarks = input('remarks/s', '');
        $uid = ret_session_name('uid');
        if (is_empty($sto_id, $sto_num, $sto_price, $uid))
        {
            $this->returnError(10000, '缺少参数');
        }
        try
        {
            if($sto_num < 0 || $sto_price < 0)
            {
                $this->returnError(10001, '金额或数量不能小于0');
            }
            $data = [
                'sto_id' => $sto_id,
                'sto_num' => $sto_num,
                'sto_single_price' => $sto_price,
                'remark' => $remarks,
                'manager' => $uid,
                'update_time' => time()
            ];
            db('goods_storage')->update($data);
            $this->returnData('', '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(50000,'系统错误' . $e->getMessage());
        }
    }

    /*
     * 入库记录删除
     */
    public function storage_del()
    {
        $this->auth_get_token();
        $sto_id = input('sto_id/d', '');
        if (is_empty($sto_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        try
        {
            db('goods_storage')->where('sto_id', '=', $sto_id)->delete();
            $this->returnData('', '删除成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '删除失败');
        }
    }

    /*
     * 出库记录列表
     */
    public function checkout_index()
    {
        $this->auth_get_token();
        $goods_name = input('goods_name/s', '');
        $org_id = request()->header('orgid');
        $limit = input('limit/d', 20);
        $page = input('page/d', 1);
        if(!$org_id){
            $this->returnError(5001, '缺少参数');
        }
        $goods_db = db('goods_detail')->where('org_id', '=', $org_id);
        if (!empty($goods_name))
        {
            $goods_db->where('goods_name', 'like', '%' . $goods_name . '%');
        }
        $goods_list = $goods_db->order('create_time DESC')->column('goods_id');
        
        try
        {
            $data = [];
            $sto_logs = db('goods_deposit')->alias('gd')->field('gd.*, u.nickname, ed.goods_name')->where('gd.goods_id', 'in', $goods_list)
                ->order('gd.create_time DESC')
                ->leftJoin('erp2_users u', 'u.uid=gd.manager')
                ->leftJoin('erp2_goods_detail ed', 'ed.goods_id=gd.goods_id')
                ->paginate($limit, false, ['page' => $page])
                ->each(function($log, $lk){
                    $log['dep_total'] = $log['dep_num'] * $log['dep_price'];
                    $log['manager']   = $log['nickname'];
                    $log['remark']    = $log['remarks'];
                    unset($log['remarks']);
                    unset($log['nickname']);
                    return $log;
                });
            $response = [
                'total' => $sto_logs->total(),
                'per_page' => $sto_logs->listRows(),
                'last_page' => $sto_logs->lastPage(),
                'data' => $sto_logs->items()
            ];
            
            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '请求失败');
        }
    }

    /*
     * 出库记录修改
     */
    public function checkout_edit()
    {
        $this->auth_get_token();
        $dep_id = input('dep_id/d', '');
        $dep_price = input('dep_price/f', '');
        $dep_num = input('dep_num/d', '');
        $remarks = input('remarks/s', '');
        if (is_empty($dep_price, $dep_price, $dep_num))
        {
            $this->returnError(10000, '缺少必填参数');
        }
        try
        {
            $data = [
                'dep_id' => $dep_id,
                'dep_num' => $dep_num,
                'dep_price' => $dep_price,
                'remarks'   => $remarks
            ];
            db('goods_deposit')->update($data);
            $this->returnData('', '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '修改失败');
        }
    }

    /*
     * 出库记录删除
     */
    public function checkout_del()
    {
        $this->auth_get_token();
        $dep_id = input('dep_id/d', '');
        if (is_empty($dep_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        try
        {
            db('goods_deposit')->where('dep_id', '=', $dep_id)->delete();
            $this->returnData('', '删除成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '删除失败');
        }
    }

    /*
     * 销售统计表
     */
    public function sale_census_index()
    {
        $this->auth_get_token();
        $cate_id = input('cate_id/d', ''); // 分类id
        $org_id = input('orgid/d', ''); // 机构id
        $sman_type = input('sman_type/d', ''); // 销售员类型, 1销售员, 2 老师
        $time_type = input('time_type/d', ''); // 1日/2月/3年
        $goods_name = input('goods_name/s', '');  // 商品名称
        $start_time = input('start_time/d', ''); // 开始时间
        $end_time = input('end_time/d', ''); // 结束时间
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        if (is_empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        //名下的商品id
        $where = [['org_id','=', $org_id]];
        if (!empty($goods_name))
        {
            $where[] = ['goods_name', 'like', '%' . $goods_name . '%'];
        }
        if ($cate_id)
        {
            $where[] = ['cate_id', '=', $cate_id];
        }
        $goods_list = db('goods_detail')->where($where)->order('create_time DESC')->column('goods_id');

        try{
                $sale_db = db('goods_sale_log')->alias('gs');
                $sale_db->where('gs.goods_id', 'in', $goods_list);
                if (!empty($sman_type))
                {
                    $sale_db->where('sman_type', '=', $sman_type);
                }
                if (!empty($start_time) and !empty($end_time))
                {
                    $sale_db->whereBetweenTime('sale_time', $start_time, $end_time);
                }elseif ($time_type)
                {
                    if ($time_type == 1) {$sale_db->whereTime('sale_time', 'd');}
                    elseif ($time_type == 2) {$sale_db->whereTime('sale_time', 'm');}
                    elseif ($time_type == 3) {$sale_db->whereTime('sale_time', 'y');}
                }
                
                static $total_amount = 0.00;
                static $total_profit = 0.00;
                $sale_logs =  $sale_db->field('gs.goods_id, ed.unit_name, gc.cate_name, ed.goods_name, COALESCE(sum(gs.sale_num),0) as stol, COALESCE(sum(gs.pay_amount),0) as sale_total, 
                           COALESCE(sum(goodsto.sto_num*goodsto.sto_single_price),0) as gmtol, COALESCE(sum(goodsto.sto_num),0) as sto_num')
                ->leftJoin('erp2_goods_storage goodsto', 'goodsto.goods_id=gs.goods_id')
                ->leftJoin('erp2_goods_detail ed', 'ed.goods_id=gs.goods_id')
                ->leftJoin('erp2_goods_cate gc', 'gc.cate_id=ed.cate_id')
                ->group('gs.goods_id')
                ->paginate($limit, false, ['page' => $page])
                ->each(function($log, $lk) use ($total_amount, $total_profit){
                    //销售数量
                    $log['sale_num'] = $log['stol'] >= 0 ? $log['stol'] : 0;
                    //入库数量
                    $log['sto_num'] >= 0 ? $log['sto_num'] : 0;
                    //入库平均单价
                    $log['avg_storage_pice'] = $log['sto_num'] == 0 ? 0 : number_format($log['gmtol']/$log['sto_num'], 2);
                    // 销售利润
                    $log['sale_profit'] = number_format($log['sale_total'] - $log['avg_storage_pice'] * $log['stol'], 2);
                    $total_amount += $log['sale_total'];
                    $total_profit += $log['sale_profit'];
                    unset($log['stol']);
                    unset($log['gmtol']);
                    return $log; 
                });
            
//            $goods_list = $goods_db->field('goods_id, cate_id, unit_name, goods_name')->order('create_time DESC')->select();
//            $data = [];
//            foreach ($goods_list as $goods)
//            {
//                $goods_id =  $goods['goods_id'];
//                $goods_name = $goods['goods_name'];
//                $unit_name = $goods['unit_name'];
////                $cate_name = db('goods_cate')->where('cate_id', '=', $cate_id)->value('cate_name');
//                $sale_db = db('goods_sale_log')->where('goods_id', '=', $goods_id);
//                if (!$sman_type)
//                {
//                    $sale_db->where('sman_type', '=', $sman_type);
//                }
//                if (!empty($start_time) and !empty($end_time))
//                {
//                    $sale_db->whereBetweenTime('sale_time', $start_time, $end_time);
//                }elseif ($time_type)
//                {
//                    if ($time_type == 1) {$sale_db->whereTime('sale_time', 'd');}
//                    elseif ($time_type == 2) {$sale_db->whereTime('sale_time', 'm');}
//                    elseif ($time_type == 3) {$sale_db->whereTime('sale_time', 'y');}
//                }
//                 销售总额
//                $sale_total_money = $sale_db->sum('pay_amount');
//                // 销售数量
//                $sale_num = $sale_db->sum('sale_num');
//                $sale_num = $sale_num > 0 ? $sale_num : 1;
//                // 入库总额
//                $sto_total_money = db('goods_storage')->where('goods_id', '=', $goods_id)
//                    ->sum('sto_num*sto_single_price');
//                // 入库数量
//                $sto_num = db('goods_storage')->where('goods_id', '=', $goods_id)
//                    ->sum('sto_num');
//                $sto_num = $sto_num > 0 ? $sto_num : 1;
//                // 入库平均单价
//                $sto_single_price = $sto_total_money / $sto_num;
//                // 销售利润
//                $sale_profit = $sale_total_money - $sto_single_price * $sale_num;

//                $data[] = [
//                    'goods_name' => $goods_name,
////                    'cate_name' => $cate_name,
//                    'unit_name' => $unit_name,
//                    'sto_num'  => $sto_num,
//                    'sale_num' => $sale_num,
//                    'sale_total' => $sale_total_money,
//                    'sale_profit' => $sale_profit
//                ];
//            }
            $response = [
                'current_page' => $sale_logs->currentPage(),
                'per_page' => $sale_logs->listRows(),
                'last_page' => $sale_logs->lastPage(),
                'total' => $sale_logs->total(),
                'total_amount' => number_format($total_amount, 2),
                'total_profit' => number_format($total_profit, 2),
                'data' => ($sale_logs->total() > 0)? $sale_logs->items(): ''
            ];
            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统异常' . $e->getMessage());
        }
    }

}
