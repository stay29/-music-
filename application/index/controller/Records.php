<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-27
 * Time: 下午4:53
 */

namespace app\index\controller;

/*
 * 销售记录，入库记录， 租借记录， 租借记录相关接口
 */


use think\Db;
use Think\Exception;

class Records extends BaseController
{

    /*
     * 4、	销售记录：销售管理模块，所有商品销售产生的销售记录均记录在此模块
            a)	搜索：输入商品名称进行搜索查看
            b)	状态筛选：不需要做状态筛选，仅有“支付成功”这一状态
            c)	导出Excel，默认保存名称为“销售记录表”，包含以下信息：
                i.	商品名称
                ii.	商品分类
                iii.	销售数量：本次销售该商品数量
                iv.	零售价（元）：商品零售价单价
                v.	销售总额（元）：该次应付款金额总额
                vi.	付款金额（元）：实付款金额
                vii.	销售时间：该销售记录的生成时间，精确到年月日
                viii.	操作员：操作生成销售记录的操作员名称
                ix.	销售单号
                x.	备注
                xi.	销售对象
                xii.	付款方式
            d)	销售记录表，包含信息：
                i.	商品名称
                ii.	所属分类
                iii.	数量：本次销售该商品数量
                iv.	零售价（元）：商品零售价单价
                v.	应付款（元）：应付款金额
                vi.	实付款（元）：实付款金额
                vii.	销售时间：该销售记录的生成时间，精确到年月日
                viii.	销售员：本条销售记录的跟进销售员名称
                ix.	销售单号
                x.	操作员：操作生成销售记录的操作员名称
                xi.	销售对象：即购物学生的名称
                xii.	付款方式
                xiii.	备注：销售商品时填写的备注
                xiv.	操作，可对该销售记录进行修改、删除操作
                1.	修改：点击则进入修改销售信息窗口，可修改销售员类型、销售员、销售数量、销售价格、销售对象、销售时间、付款方式及备注等等信息
                2.	删除该条销售记录数据
                5、	租赁记录
     */


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

            $goods_list = $db->field('goods_id, goods_name, cate_id')->select();
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
         *  c)
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
        if (empty($org_id))
        {
            $this->returnError(10000, '缺少机构ID');
        }
        try{
            // 租客
            $rent_obj_id = db('students')->
                where('truename', 'like', '%' . $key . '%')->value('stu_id');
            $goods_id = db('goods_detail')->
                where('goods_name', 'like', '%' . $key . '%')->value('goods_id');
            $table = db('goods_rental_log')->
            whereOr('rent_obj_id', '=', $rent_obj_id)->where('status', '=', $status)->whereOr('goods_id', '=', $goods_id);
            if (!empty($start_time) and !empty($end_time))
            {
                $table = $table->whereBetweenTime('create_time',  $start_time,  $end_time);
            }
            $total_margin = $table->sum('rent_margin'); // 总押金
            $total_amount = $table->sum('rent_amount');  // 总租金
            $total_prepaid_rent = $table->sum('prepaid_rent');  // 总预收租金
            $data = [
                'total_margin' => $total_margin,
                'total_amount' => $total_amount,
                'total_prepaid_rent' => $total_prepaid_rent,
                'records' => array()
            ];
            $logs = $table->select();   //
            foreach ($logs as $log) {
                $g_id = $log['goods_id'];
                $rent_id = $log['rent_id'];
                $rent_obj_type = $log['rent_onj_type'];
                $rent_obj_id = $log['rent_obj_id'];
                $goods_name = db('goods_detail')->where('goods_id', '=', $g_id)->value('goods_name');
                $rent_obj_name = '其他';
                if ($rent_obj_type == 1)  // 1是学生， 2是其他对象
                {
                    $rent_obj_name = db('students')->where('stu_id', '=', $rent_obj_id)
                        ->value('truename');
                }
                $rent_num = $log['rent_num'];
                $start_time = $log['start_time'];
                $end_time = $log['end_time'];
                $rent_type = $rent_type_arr[$log['rent_type']]; // 租借方式
                $rent_type_money = db('goods_detail')->      // 租借方式
                //对应的租金
                where('goods_id', '=', $g_id)->value($rent_type_amount_arr[$log['rent_type']]);
                $rent_amount = $log['rent_amount'];  // 租金金额
                $prepaid_rent = $log['prepaid_rent']; // 预付租金
                $status = $log['status'];
                if (time() > $log['end_time'] and $status != 3) // 超时未归还
                {
                    $status = 2;
                }
                $status_text = $status_arr[$status];    // 租凭状态对应文字
                $remarks = $log['remarks'];
                $data['records'] = [
                    'rent_id' => $rent_id,  // 租借记录id
                    'goods_name' => $goods_name,
                    'rent_code' => $log['rent_code'], // 租借单号
                    'rent_obj_name' => $rent_obj_name, // 租借对象姓名
                    'rent_obj_id'   => $rent_obj_id,    // 租借对象id
                    'rent_obj_type' => $rent_obj_type,  // 租借对象类型1学生, 其他
                    'rent_num'  => $rent_num,   // 租借数量
                    'start_time' => $start_time,    // 租借开始时间
                    'end_time'  => $end_time,   // 租借结束时间
                    'rent_type' => $rent_type,  // 租借类型
                    'rent_type_money' => $rent_type_money,  // 租借类型对应租金
                    'rent_amount' => $rent_amount,  // 租金
                    'prepaid_rent' => $prepaid_rent,    // 预付租金
                    'status' => $status,    // 租借状态
                    'status_text' => $status_text,
                    'remarks' => $remarks,
                    'pay_id' => $log['pay_id'], // 支付方式
                ];
            }
            $this->returnData($data, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '如果你看到这个，证明有Bug');
        }
    }

    /*
     * 租赁记录详情表
     */
    public function rental_detail()
    {
        $this->auth_get_token();
        $rent_id = input('rent_id/d', '');
        if (is_empty($rent_id)) {
            $this->returnData(10000, '缺少参数');
        }
        try
        {
            $log = db('goods_rental_log')->where('rent_id', '=', $rent_id)->find();
            $goods_name = db('goods_detail')->
                where('goods_id', '=', $log['goods_id'])->value('goods_name');
            $rent_obj_name = '其他';
            if ($log['rent_obj_type'] == 1)
            {
                $rent_obj_name = db('students', '=', $log['rent_obj_id'])->value('truename');
            }
            // 每天费用
            $rent_amount_day = $this->get_amount_of_day($log['rent_type'], $log['goods_id']);
            $interval_time = timediff($log['start_time'], time());
            $pay_amount = $rent_amount_day * $interval_time['day']; // 实际租金
            $refund_amount = $log['prepaid_rent'] + $log['rent_margin'] - $pay_amount;
            $data = [
                'rent_id' => $log['rent_id'],
                'goods_name' => $goods_name, // 商品名称
                'rent_num' => $log['rent_num'], // 租借数量
                'rent_type' => $log['rent_type'], // 租借类型
                'rent_margin' => $log['rent_margin'], // 租凭押金
                'rent_obj_name' => $rent_obj_name,
                'prepaid_rent' => $log['prepaid_rent'], // 预付租金
                'start_time'    => $log['start_time'],
                'end_time'  => $log['end_time'],
                'remarks'   => $log['remarks'],
                'pay_id'    => $log['pay_id'],      // 支付方式id
                'pay_amount' => $pay_amount,  // 实际付款
                'refund_amount' => $refund_amount, // 实际退款
            ];
            $this->returnData($data, '请求成功');
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
        $remarks = input('');
        if (is_empty($rent_id, $rent_margin, $prepaid_rent, $end_time, $remarks))
        {
            $this->returnError(10000, '缺少参数');
        }
        try
        {
            $data = [
                'rent_margin' => $rent_margin,
                'prepaid_rent' => $prepaid_rent,
                'end_time' => $end_time,
                'remarks' => $remarks
            ];
            db('goods_rental_log')->where('rent_id')->update($data);
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
        if (is_empty($goods_name, $org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $data = [];
        $goods_list = db('goods_detail')->field('goods_id, goods_name')
            ->where('goods_name', 'like', '%' . $goods_name . '%')->select();
        try
        {
            foreach ($goods_list as $goods)
            {
                $goods_id = $goods['goods_id'];
                $g_name = $goods['goods_name'];
                $sto_logs =  db('goods_storage')->field('sto_id, sto_num, sto_single_price, sto_code, 
                    entry_time, manager')->where('goods_id', '=', $goods_id)->select();
                foreach ($sto_logs as $log)
                {
                    $manager = db('users')->where('uid', '=', $log['manager'])->value('nickname');
                    $data[] = [
                        'sto_id' => $log['sto_id'],
                        'goods_name'  => $g_name,
                        'sto_single_price'   => $log['sto_single_price'],
                        'sto_num'   => $log['sto_num'],
                        'sto_code'  => $log['sto_code'],
                        'entry_time' => $log['entry_time'],
                        'sto_total_money' => $log['sto_num'] * $log['sto_single_price'],
                        'manager' => $manager
                     ];
                }
            }
            $response = [
                'total' => count($data),
                'per_page' => $limit,
                'last_page' => count($data) / $limit + 1,
                'data' => array_slice($data, ($page-1)*$limit, $limit)
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
        $uid = input('uid/d', '');
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
                'sto_price' => $sto_price,
                'remarks' => $remarks,
                'manager' => $uid,
                'update_time' => time()
            ];
            db('goods_storage')->update($data);
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
        $org_id = input('orgid/d', '');
        $limit = input('limit/d', 20);
        $page = input('limit/d', 1);
        $goods_db = db('goods_detail')->where('org_id', '=', $org_id);
        if (!empty($goods_name))
        {
            $goods_db->where('goods_name', 'like', '%' . $goods_name . '%');
        }
        try
        {
            $goods_list = $goods_db->field('goods_id, goods_name')->select();
            $data = [];
            foreach ($goods_list as $goods)
            {
                $g_name = $goods['goods_name'];
                $g_id = $goods['goods_id'];

                $sto_logs = db('goods_deposit')->where('goods_id', '=', $g_id)->select();
                foreach ($sto_logs as $log)
                {
                    $manager = db('users')->where('uid', '=', $log['manager'])->value('nickname');
                    $data[] = [
                        'dep_id' => $log['dep_id'],
                        'goods_name' => $g_name,
                        'dep_num'   => $log['dep_num'],
                        'dep_price'  => $log['dep_price'],
                        'dep_total'  => $log['dep_num'] * $log['dep_price'],
                        'dep_time'  => $log['dep_time'],
                        'dep_code'  => $log['dep_code'],
                        'manager'   => $manager,
                        'remark'    => $log['remark'],
                    ];
                }
            }
            $response = [
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => count($data) / $limit + 1,
                'data' => array_slice($data, ($page-1)*$limit, $limit)
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
     * 8、	销售统计：商品销售统计信息管理模块
            a)	分类筛选：选择商品所属分类以筛选查看
            b)	选择销售员：（与艺点点不同）选择销售员类型，可选老师或销售员，默认为全部
            c)	日期筛选：（与艺点点不同）可单选全部、本月、上月、本年，默认为全部，也可自定义选择日期区间，分别选择起始日期、截止日期
            d)	搜索：通过输入商品名称进行搜索查看
            e)	总金额&总利润：显示当前筛选条件下的总销售金额和总利润
            f)	导出Excel：默认保存名称为“销售统计”，包含字段：
            i.	商品名称
            ii.	单位
            iii.	入库量
            iv.	销售量
            v.	销售额
            vi.	利润
            g)	刷新：刷新当前销售统计表
            h)	销售统计表，包含信息：
            i.	商品名称
            ii.	入库量：该商品总的入库数量，同时显示单位
            iii.	销售量：该商品销售总量
            iv.	销售额（元）：商品销售总额
            v.	平均成本（元）：即该商品的平均成本，计算规则：平均成本=入库总额/入库量
            vi.	利润（元）：该商品销售获得的总利润，计算规则：总利润=销售额-平均成本*销售量
     */

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
        try{
            $goods_db = db('goods_detail')->where('org_id', '=', $org_id);
            if (!empty($goods_name))
            {
                $goods_db->where('goods_name', 'like', '%' . $goods_name . '%');
            }
            if ($cate_id)
            {
                $goods_db->where('cate_id', '=', $cate_id);
            }
            $goods_list = $goods_db->field('goods_id, cate_id, unit_name, goods_name')->select();
            $data = [];
            foreach ($goods_list as $goods)
            {
                $goods_id =  $goods['goods_id'];
                $goods_name = $goods['goods_name'];
                $unit_name = $goods['unit_name'];
//                $cate_name = db('goods_cate')->where('cate_id', '=', $cate_id)->value('cate_name');
                $sale_db = db('goods_sale_log')->where('goods_id', '=', $goods_id);
                if (!$sman_type)
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
                // 销售总额
                $sale_total_money = $sale_db->sum('pay_amount');
                // 销售数量
                $sale_num = $sale_db->sum('sale_num');
                $sale_num = $sale_num > 0 ? $sale_num : 1;
                // 入库总额
                $sto_total_money = db('goods_storage')->where('goods_id', '=', $goods_id)
                    ->sum('sto_num*sto_single_price');
                // 入库数量
                $sto_num = db('goods_storage')->where('goods_id', '=', $goods_id)
                    ->sum('sto_num');
                $sto_num = $sto_num > 0 ? $sto_num : 1;
                // 入库平均单价
                $sto_single_price = $sto_total_money / $sto_num;
                // 销售利润
                $sale_profit = $sale_total_money - $sto_single_price * $sale_num;

                $data[] = [
                    'goods_name' => $goods_name,
//                    'cate_name' => $cate_name,
                    'unit_name' => $unit_name,
                    'sto_num'  => $sto_num,
                    'sale_num' => $sale_num,
                    'sale_total' => $sale_total_money,
                    'sale_profit' => $sale_profit
                ];
            }
            $response = [
                'current_page' => $page,
                'per_page' => $limit,
                'last_page' => (count($data) / $limit) +1,
                'total' => count($data),
                'data' => array_slice($data, ($page-1)*$limit, $limit)
            ];
            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统异常' . $e->getMessage());
        }
    }

}



