<?php
/**
 * 进销存模块
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-14
 * Time: 下午4:50
 */

namespace app\index\controller;
use think\Db;
use think\Exception;
use think\facade\Log;

use app\index\model\Goods as GoodsModel;
use app\index\validate\Goods as GoodsValidate;

/*
 * 商品管理，分类管理，销售员管理，以及商品相关操作。
 */


final class Categories
{
    /*
     * 获取分类首页显示的分类信息
     */
    static public function getIndexCate($array, $pid=0, $level=0)
    {

        static $list = array();
        // 分类等级显示 need Chinese。
        static $map = ['顶', '二', '三', '四', '五', '六', '七', '八', '九', '十'];
        foreach ($array as $key=>$value)
        {
            if ($value['cate_pid'] == $pid)
            {
                $value['level_num'] = $level;
                $value['level_text'] = $map[$level] . '级分类';
                $list[] = $value;
                unset($array[$key]);
                self::getIndexCate($array, $value['cate_id'], $level+1);
            }
        }
        return $list;
    }
}



class Goods extends BaseController
{
    /*
     * 分类列表
     */
    public function cate_index()
    {
        $this->auth_get_token();
        $org_id = input('orgid/d', '');
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        if(empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $categories_raw = db('goods_cate')->field('cate_id, cate_pid, cate_name')
            ->order('order, create_time AES')->where('org_id', '=', $org_id)->select();
        $categories = [];
        foreach ($categories_raw as $v)
        {
            $cate_pname =  db('goods_cate')->
                where('cate_id', '=', $v['cate_pid'])->value('cate_name');
            $v['cate_pname'] = $cate_pname ? $cate_pname : '顶级分类';
            $categories[] = $v;
        }
        $data = Categories::getIndexCate($categories);
        $response = [
            'total' => count($data),
            'per_page' => $limit,
            'last_page' => ceil(count($data)/$limit),
            'data' => array_slice($data, ($page-1)*$limit, $limit)
        ];
        $this->returnData($response, '请求成功');
    }

    /*
     * 添加分类
     */
    public function cate_add()
    {
        $this->auth_get_token();
        $org_id = input('orgid/d', '');
        $cate_pid = input('cate_pid', 0);
        $cate_name = input('cate_name', '');
        $order = input('order', 0);
        if(is_empty($org_id, $cate_name))
        {
            $this->returnError(10000, '缺少参数');
        }
        if(strlen($cate_name) > 20)
        {
            $this->returnError( 10001, '分类名称字符过长');
        }
        if ($cate_pid != 0)
        {
            $cate = Db::name('goods_cate')
                ->field('cate_name')->where(['cate_id' => $cate_pid, 'org_id'=>$org_id])->find();
            if(empty($cate))
            {
                $this->returnError(20001, '添加失败，父级分类不存在');
//                $this->return_data(0, '20001', '添加失败, 父级分类不存在', false);
            }
        }
        $data = [
            'cate_pid' => $cate_pid,
            'cate_name' => $cate_name,
            'org_id'  => $org_id,
            'order' => $order
        ];
        Db::startTrans();
        try
        {
            Db::name('goods_cate')->insertGetId($data);
            Db::commit();
            $this->returnData(1, '添加成功');
        }catch (Exception $e)
        {
            $this->returnError('20001', '添加失败');
            Db::rollback();
        }
    }

    /*
     * 修改分类
     */
    public function cate_edit()
    {
        $this->auth_get_token();
        $cate_id = input('cate_id/d', '');
        $cate_name = input('cate_name/s', '');
        $order = input('order/d', 0);
        $cate_pid = input('cate_pid/d', '');
        $org_id = input('orgid/d', '');
        if(is_empty($cate_name, $org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        if($cate_id == $cate_pid){
           $this->returnError(10000, '上级分类不能为自己'); 
        }
        Db::startTrans();
        try
        {
            $p_cate = db('goods_cate')->where('cate_id', '=', $cate_pid)->select();
            if (empty($p_cate) && $cate_pid != 0)
            {
                $this->returnError( '10000', '父级分类不存在');
            }
            $data = [
                'cate_name' => $cate_name,
                'cate_pid'  => $cate_pid,
            ];
            Db::name("goods_cate")->where(['org_id'=>$org_id, 'cate_id'=>$cate_id])->update($data);
            Db::commit();
            $this->returnData( '', '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(20002, '修改失败'.$e->getMessage());
        }
    }

    /**
     * 添加分类或修改分类时的下拉显示列表
     */
    public function cate_list()
    {
        $this->auth_get_token();
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        $categories = db('goods_cate')->field('cate_id as id,  cate_pid, cate_name')->
            order('order, create_time DESC')->where('org_id', '=', $org_id)->select();
        $data = getTree($categories);
        $response = $data;
//        $response = [
//            [
//                'id' => 0,
//                'cate_name' => '顶级分类',
//                'sub' => $data
//            ]
//        ];
//        array_push($data, $top_cate);
        $this->returnData($response, '请求成功');
    }


    /**
     * 删除分类
     */



    public function cate_del()
    {
        $this->auth_get_token();
        $cate_id = input('cate_id/d', '');
        $org_id = input('orgid/d', '');
        if(is_empty($cate_id, $org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $son_cate = Db::name('goods_cate')->where(['org_id'=>$org_id, 'cate_pid'=>$cate_id])->select();
        if (!empty($son_cate))
        {
            $this->returnError(20002, '请先删除子分类');
        }
        Db::startTrans();
        try{
            Db::name('goods_cate')->where(['cate_id'=>$cate_id, 'org_id'=>$org_id])->delete();
            Db::commit();
            $this->returnData('', '删除成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError(20003, '删除失败');
        }
    }

    /*
     * 销售员列表
     */
    public function mans_index()
    {
        $this->auth_get_token();
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        $data = db('salesmans')->where('org_id', '=', $org_id)
            ->field('sm_id, sm_name, sm_mobile, status')->order('create_time DESC')
            ->paginate($limit);
        $this->returnData($data, '请求成功');
    }

    /*
     * 全部销售员列表
     */
    public function all_mans_index()
    {
        $this->auth_get_token();
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $data = db('salesmans')->where('org_id', '=', $org_id)->where('status', '=', 1)
            ->field('sm_id, sm_name')->order('create_time DESC')->select();
        $this->returnData($data, '请求成功');
    }

    /*
     * 全部学生列表
     */
    public function all_students()
    {
        $this->auth_get_token();
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $data = db('students')->where(['org_id'=> $org_id])->field('stu_id, truename as stu_name')
            ->order('create_time DESC')
            ->select();
        $response = [];
        foreach ($data as $k=>$v)
        {
            $stu_id = $v['stu_id'];
            $res = db('stu_balance')->where('stu_id', '=', $stu_id)->field('gift_balance, recharge_balance')->find();
            $stu_balance = $res['gift_balance'] + $res['recharge_balance'];
            $response[] = [
                'stu_id' => $stu_id,
                'stu_name' => $v['stu_name'],
                'stu_balance' => $stu_balance
            ];
        }
        $this->returnData($response, '请求成功');
    }


    /*
     * 全部支付方式列表
     */
    public function all_pay_list()
    {
        $this->auth_get_token();
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $data = db('payments')->field('pay_id, payment_method as pay_name')
            ->where('status', '=', 1)->order('create_time DESC')->select();
        $response = [];
        foreach ($data as $k=>$v)
        {
            if ($v['pay_name'] == '账户余额')
            {
                $v['is_only_stu'] = 1;
            }
            else
            {
                $v['is_only_stu'] = 0;
            }
            $response[] = $v;
        }
        $this->returnData($response, '请求成功');
    }

    /*
     * 删除销售员
     */
    public function mans_del()
    {
        $this->auth_get_token();
        $sm_id = input('sm_id/d', '');
        if(is_empty($sm_id))
        {
            $this->returnError(10000,'缺少参数');
        }
        db('salesmans')->where('sm_id', '=', $sm_id)->delete();
        $this->returnData(true, '删除成功');
    }

    /*
     * 修改销售员
     */
    public function mans_edit()
    {
        $this->auth_get_token();
        $data = [
            'sm_id' => input('sm_id/d', ''),
            'sm_name' => input('sm_name/s', ''),
            'org_id' => input('orgid/d', ''),
            'sm_mobile' => input('sm_mobile', ''),
            'status' => input('status')
        ];
        if (is_empty($data['sm_id'], $data['org_id'], $data['sm_mobile']))
        {
            $this->returnError(10000,'缺少参数');
        }
        try{
            $data['update_time'] = time();
            db('salesmans')->update($data);
            $this->returnData('', '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(20002, '系统错误, 修改失败');
        }
    }

    /*
     * 添加销售员
     */
    public function mans_add()
    {
        $this->auth_get_token();
        $data = [
//            'sm_id' => input('sm_id/d', ''),
            'sm_name' => input('sm_name/s', ''),
            'org_id' => input('orgid/d', ''),
            'sm_mobile' => input('sm_mobile', ''),
            'status' => input('status')
        ];
        foreach ($data as $k => $v)
        {
            if (empty($v))
            {
                $this->returnError(10000, '缺少参数');
            }
        }
        try{
            $data['create_time'] = time();
            $data['update_time'] = time();
            db('salesmans')->insert($data);
            $this->returnData(1,  '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(20002, '系统错误, 修改失败');
        }
    }

    /*
     * 销售员离职
     */
    public function mans_departure()
    {
        $this->auth_get_token();
        $sm_id = input('sm_id/d', '');
        if(empty($sm_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        try
        {
            db('salesmans')->where('sm_id', '=', $sm_id)->update(['status'=>2]);
            $this->returnData( '', '离职成功');
        }catch (Exception $e)
        {
            log($e->getMessage());
            $this->returnError('50000', '离职失败');
        }
    }

    /*
     * 销售员复职
     */
    public function mans_recovery()
    {
        $this->auth_get_token();
        $sm_id = input('sm_id/d', '');
        if(empty($sm_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        try{
            db('salesmans')->where('sm_id', '=', $sm_id)->update(['status'=>1]);
            $this->returnData(1, '复职成功');
        }catch (Exception $e)
        {
            log($e->getMessage());
            $this->returnError(50000, '离职失败');
        }
    }
    
    /**
     * 找子孙分类
     * 
     */
    private function find_sons($cate_id)
    {
        static $inda = [];
        array_push($inda, $cate_id);
        $sons = db('goods_cate')->where('cate_pid', $cate_id)->column('cate_id');
        if($sons){
            foreach ($sons as $k => $son) {
                $this->find_sons($son, $inda);
            }
        }
        return $inda;
    }
    
    /*
     * 商品列表
     */
    public function index()
    {
        $this->auth_get_token();
        $org_id = input('orgid' , '');
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        $cate_id = input('cate_id/d', '');
        $goods_name = input('goods_name/s', '');
        if(empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }

        $db = db('goods_detail')->field('goods_id, goods_name, remarks,
        unit_name, cate_id, margin_amount, goods_amount, goods_img')->order('create_time DESC')->where('org_id', '=', $org_id);
        if (!empty($cate_id))
        {
            $inda = $this->find_sons($cate_id);
            $db->where('cate_id', 'in', $inda);
        }
        if(!empty($goods_name) || $goods_name==0)
        {
            $db->where('goods_name', 'like', '%' . $goods_name . '%');
        }
        $goods_list = $db->order('create_time DESC')->paginate($limit);
        // 返回值
        $response = [
            'total' => $goods_list->total(),
            'per_page' => $limit,
            'current_page' => $goods_list->currentPage(),
            'last_page' => $goods_list->lastPage(),
            'data' => array(),
        ];

        try
        {
            foreach ($goods_list as $goods)
            {
                $goods_id = $goods['goods_id'];
                // 分类名称
                $goods['cate_name'] = db('goods_cate')->where(['cate_id'=>$goods['cate_id']])
                    ->value('cate_name');

//            // 入库均价
//            $avg_sql ="SELECT (sum(sto_num*sto_single_price)/sum(sto_num))
//                        as avg_sto_price FROM erp2_goods_storage WHERE goods_id={$goods['goods_id']}";

                // 入库总量
                $sto_total_num = db('goods_storage')->where(['goods_id'=>$goods_id])->sum('sto_num');
//                $this->returnData($sto_total_num);
                $sql = "SELECT sum(sto_single_price * sto_num) as sto_total FROM erp2_goods_storage WHERE goods_id={$goods_id}";
                $res = Db::query($sql)[0]['sto_total'];
                // 入库总额
                $sto_total_money = $res?$res:0;

                // 入库平均单价
                $sto_avg_money = $sto_total_num?sprintf("%.2f", ($res/$sto_total_num)) : 0;

                // 出库总量
                $dep_total_num = db('goods_deposit')->where(['goods_id'=>$goods_id])->sum('dep_num');
                // 出库总额
                $sql = "SELECT sum(dep_price*dep_num) as dep_total FROM erp2_goods_deposit WHERE goods_id={$goods_id};";
                $res = Db::query($sql)[0]['dep_total'];
                $dep_total_money = $res ? $res : 0;
//                $dep_total_money = db('goods_deposit')->where(['goods_id'=>$goods_id])->sum('dep_price*dep_num');
                // 出库均价
                $dep_avg_money = db('goods_deposit')->where('goods_id', '=', $goods_id)->avg('dep_price');
                // 销售总额
                $sql = "SELECT sum(single_price * sale_num) as sale_total FROM erp2_goods_sale_log WHERE goods_id={$goods_id};";
                $res = Db::query($sql)[0]['sale_total'];
                $sale_total_money = $res ? $res : 0;
                // 商品库存
                $goods['goods_sku'] = db('goods_sku')->where(['goods_id'=>$goods_id])->value('sku_num');

                $goods['sto_total_num'] = $sto_total_num;
                $goods['sto_total_money'] = $sto_total_money;
                $goods['sto_avg_money'] = $sto_avg_money;

                $goods['dep_total_money'] = $dep_total_money;
                $goods['dep_total_num'] = $dep_total_num;
                $goods['dep_avg_money'] = $dep_avg_money;

                $goods['sale_total_money'] = $sale_total_money;
                $response['data'][] = $goods;
                unset($goods);
            }
            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            Log::write($e->getMessage());
            $this->returnError(50000, '系统出错' . $e->getMessage());
//            $this->return_data(0, '50000', '系统出错');
        }
    }

    /*
     * 添加商品
     */
    public function add()
    {
        $this->auth_get_token();
        $uid = input('uid');
        $data = input('post.');
        $data['org_id'] = input('orgid');
        $data['goods_amount'] = input('goods_amount/f', 0.0);
        $data['rent_amount_day'] = input('rent_amount_day/f', 0.0);
        $data['rent_amount_mon'] = input('rent_amount_mon/f', 0.0);
        $data['rent_amount_year'] = input('rent_amount_year/f', 0.0);
        $data['margin_amount'] = input('margin_amount/f', 0.0);
        $data['remarks'] = input('remarks/s', '');
        $data['manager'] = $uid;
        try{
            $validate = new GoodsValidate();
            if (!$validate->check($data))
            {
                $error = explode('|', $validate->getError());
                $this->returnError($error[0], $error[1]);
            }
            if ($data['cate_id'] == 0)
            {
                $this->returnError(10000, '分类有误');
            }
            $goods = new GoodsModel($data);
            $goods->save();
            $goods_id = $goods->goods_id;
            Db('goods_sku')->insert(['goods_id' => $goods_id]);
            $this->returnData(1, '添加成功');
        }catch (Exception $e)
        {
            Log::write($e->getMessage());
            $this->returnError( '50000', '系统错误'.$e->getMessage());
        }
    }

    /*
     * 删除商品
     */
    public function del()
    {
        $this->auth_get_token();
        $goods_id = input('goods_id', '');
        if(empty($goods_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        Db::startTrans();
        try
        {
            // 出库记录
            Db::name('goods_deposit')->where('goods_id', '=', $goods_id)->delete();
            // 租凭记录
            Db::name('goods_rental_log')->where('goods_id', '=', $goods_id)->delete();
            // 销售记录
            Db::name('goods_sale_log')->where('goods_id', '=', $goods_id)->delete();
            // 库存记录
            Db::name('goods_sku')->where('goods_id', '=', $goods_id)->delete();
            // 入库记录
            Db::name('goods_storage')->where('goods_id', '=', $goods_id)->delete();
            // 商品记录
            Db::name('goods_detail')->where('goods_id', '=', $goods_id)->delete();
            Db::commit();
            $this->returnData(1,  '删除成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError(50000, '系统错误');
        }
    }

    /*
     * 修改商品
     */
    public function edit()
    {
        $this->auth_get_token();
        $goods_id = input('goods_id/d', '');
        if (is_empty($goods_id))
        {
            $this->returnError(10000, '商品id必填');
        }
        $data = input('post.');
        $data['manager'] = input('post.uid/d');
        $data['org_id'] = input('orgid/d');
        try{
            $validate = new GoodsValidate();
            if(!$validate->check($data))
            {
                $error = explode('|', $validate->getError());
                $this->returnError( $error[0], $error[1]);
            }
            GoodsModel::update($data);
            $this->returnData(1, '修改成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统出错' . $e->getMessage());
        }
    }

    /*
     * 商品详情
     */
    public function detail()
    {
        $this->auth_get_token();
        $goods_id = input('goods_id/d', '');
        if (is_empty($goods_id))
        {
            $this->returnError(10000, '缺少参数');
        }
//        $log = db('goods_rental_log')->where('goods_id', '=', $goods_id)->find();
//        $goods_name = db('goods_detail')->where('goods_id', '=', $goods_id)->value('goods_name');
//        $rent_obj_name = '其他';
//        if ($log['rent_obj_type'] == 1) // 学生
//        {
//            $rent_obj_name = db('students')->where('stu_id')->value('truename');
//        }
        $data = db('goods_detail')->where('goods_id', '=', $goods_id)->order('create_time')->find();
        $this->returnData($data, '请求成功');
    }


    /*
     * 商品入库
     */
    public function storage()
    {
        $this->auth_get_token();
        $uid = input('uid/d', '');
        $goods_id = input('goods_id/d', '');
        $goods_num = input('goods_num/d', '');
        $goods_price = input('goods_price/f', '');
        $remark = input('remark/s', '');
        $entry_time = input('entry_time/d', time());
        if(is_empty($goods_id, $goods_num, $goods_price, $uid))
        {
            $this->returnError('10000', '缺少参数');
        }
        if ($goods_num <= 0 || $goods_price <= 0)
        {
            $this->returnError(10000, '商品数量和价格必须大于0');
        }
        if (strlen($remark) > 200)
        {
            $this->returnError('10000', '备注不能超过200字符');
        }
        Db::startTrans();
        try
        {
            $goods_id = Db::name('goods_detail')->where('goods_id', '=', $goods_id)->value('goods_id');
            if (empty($goods_id))
            {
                $this->returnError('20001', '入库失败');
            }
            $sku = Db::name('goods_sku')->where('goods_id', '=', $goods_id)->find();
            if (!empty($sku))
            {
                $sku_id = $sku['sku_id'];
                $sku_num = $sku['sku_num'] + $goods_num;
                Db::name('goods_sku')->where('sku_id', '=', $sku_id)->update(['sku_num' => $sku_num]);
            }
            else{
                $sku_data = [
                    ['goods_id', '=', $goods_id],
                    ['sku_num', '=', $goods_num]
                ];
                Db::name('goods_sku')->insert($sku_data);
            }
            $sto_data = [
                'goods_id' => $goods_id,
                'sto_num' => $goods_num,
                'sto_single_price' => $goods_price,
                'remark'    => $remark,
                'entry_time' => $entry_time,
                'create_time' => time(),
                'update_time' => time(),
                'sto_code'  => random_code(),
                'manager' => $uid
            ];
            Db::name('goods_storage')->insert($sto_data);
            Db::commit();
            $this->returnData(1,  '入库成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError(20001, '入库失败' . $e->getMessage());
        }
    }

    /*
     * 商品出库
     */
    public function checkout()
    {
        $this->auth_get_token();
        $uid = input('uid');
        $goods_id = input('goods_id/d', '');
        $dep_num = input('dep_num/d', '');
        $dep_price = input('dep_price', '');
        $dep_time = input('dep_time/d', time());
        $remarks = input('remark/s', '');
        if (is_empty($goods_id, $dep_num, $dep_price))
        {
            $this->returnError('10000', '缺少参数', '');
        }
        if (mb_strlen($remarks) > 200)
        {
            $this->returnError( '10000', '备注200字符内');
        }
        if ($dep_num <= 0 || $dep_price <= 0 )
        {
            $this->returnError('10000', '商品价格和数量不能小于0');
        }
        Db::startTrans();
        try
        {
            // 校验库存
            $sku_num = Db::name('goods_sku')->where('goods_id', '=', $goods_id)->value('sku_num');
            if ($sku_num < $dep_num)
            {
                $this->returnError( '20001', '出库失败,库存不足');
            }
            if ($sku_num < 1 || $sku_num < $dep_num)
            {
                $this->returnError(10001, '库存不足');
            }
            // 出库
            $sku_num -= $dep_num;
            Db::name('goods_sku')->where('goods_id', '=', $goods_id)->update(['sku_num' => $sku_num]);
            $dep_data = [
                'goods_id' => $goods_id,
                'dep_num' => $dep_num,
                'dep_price' => $dep_price,
                'dep_time' => $dep_time,
                'remarks' => $remarks,
                'create_time'   => time(),
                'update_time'    => time(),
                'dep_code'  => random_code(),
                'manager' => $uid,
            ];
            // 出库记录
            Db::name('goods_deposit')->insert($dep_data);
            Db::commit();
            $this->returnData('', '出库成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError('50000', '系统出错, 出库失败' . $e->getMessage());
        }
    }

    /*
     * 商品销售
     */
    public function sale()
    {
        $this->auth_get_token();
        $uid = input('uid/d', '');
        $goods_id = input('goods_id/d', '');  // 商品id
        $sman_type = input('sman_type/d', ''); //销售员类型, 1销售员,2老师
        $sman_id = input('sman_id/d', ''); // 销售员id或教师id
        $sale_obj_type = input('sale_obj_type', ''); // 销售对象,1学生2其他
        $sale_obj_id = input('sale_obj_id', ''); // 学生id, 或其他则为0
        $pay_id = input('pay_id/d', ''); // 支付方式
        $sale_num = input('sale_num/d', ''); // 销售数量
        $single_price = input('single_price/f', 0.0);   // 单价
        $sum_payable = input('sum_payable/f', $single_price);     // 应付金额
        $remark = input('remark/s', ''); // 备注
        $sale_time = input('sale_time/d', time()); // 销售时间
        $pay_amount = input('pay_amount/f', 0.00);  // 实付金额

        $create_time = time();      // 创建时间
        $update_time = time();      // 更新时间
        $sale_code = random_code();  // 销售单号
        if (is_empty($goods_id, $sman_type, $sman_id, $sale_obj_type, $sale_obj_id, $pay_id))
        {
            if ($sale_obj_type!=2 && $sale_obj_id!=0)
            {
                $this->returnError('10000', '缺少必填参数');
            }
        }
        Db::startTrans();
        try
        {
            $sale_data = [
                'goods_id' => $goods_id,
                'sale_code'    => $sale_code,
                'sman_type'     => $sman_type,
                'sman_id'  => $sman_id,
                'sale_num'  => $sale_num,
                'sale_obj_type' => $sale_obj_type,
                'sale_obj_id'  => $sale_obj_id,
                'single_price' => $single_price,
                'sum_payable' => $sum_payable,
                'pay_amount' => $pay_amount,
                'pay_id' => $pay_id,
                'sale_time' => $sale_time,
                'remark' => $remark,
                'create_time' => $create_time,
                'update_time' => $update_time,
                'manager' => $uid,
            ];
            $sku_num = Db::name('goods_sku')->where('goods_id', '=', $goods_id)->value('sku_num');
            if ($sku_num < 1 ||  $sku_num < $sale_num)
            {
                $this->returnError(10001, '库存不足');
            }
            //是否余额支付
            if($pay_id === BALANCE_PAY && $sale_obj_type == 1 && $sale_obj_id){
                $res = db('stu_balance')->where('stu_id', '=', $sale_obj_id)->field('gift_balance, recharge_balance')->find();
                $stu_balance = $res['gift_balance'] + $res['recharge_balance'];
                if($stu_balance < $pay_amount){
                   $this->returnError(10001, '余额不足'); 
                }
            }
            Db::name('goods_sale_log')->insert($sale_data);
            $sku_num -= $sale_num;
            Db::name('goods_sku')->where('goods_id', '=', $goods_id)->update(['sku_num' => $sku_num]);
            Db::commit();
            $this->returnData(1, '销售成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError(50000, '系统出错，销售失败' . $e->getMessage());
        }
    }
    
    /*
     * 学生模块多个销售
     */
    public function multi_sale(){
        $this->auth_get_token();
        $uid = input('uid/d', '');
        $goods_id = input('goods_id', '');  // 商品id
        $sman_type = input('sman_type/d', ''); //销售员类型, 1销售员,2老师
        $sman_id = input('sman_id/d', ''); // 销售员id或教师id
        $sale_obj_type = input('sale_obj_type', ''); // 销售对象,1学生2其他
        $sale_obj_id = input('sale_obj_id', ''); // 学生id, 或其他则为0
        $pay_id = input('pay_id/d', ''); // 支付方式
        $sale_num = input('sale_num', ''); // 销售数量
        $single_price = input('single_price', '');   // 单价
        $remark = input('remark/s', ''); // 备注
        $sale_time = input('sale_time/d', time()); // 销售时间

        if (is_empty($goods_id, $sman_type, $sman_id, $sale_obj_type, $sale_obj_id, $pay_id))
        {
            if ($sale_obj_type!=2 && $sale_obj_id!=0)
            {
                $this->returnError('10000', '缺少必填参数');
            }
        }
        Db::startTrans();
        try
        {
            $comman_data = [
                'sman_type'     => $sman_type,
                'sman_id'  => $sman_id,
                'sale_obj_type' => $sale_obj_type,
                'sale_obj_id'  => $sale_obj_id,
                'pay_id' => $pay_id,
                'sale_time' => $sale_time,
                'remark' => $remark,
                'create_time' => time(),
                'update_time' => time(),
                'manager' => $uid,
            ];
            //是否余额支付
            if($pay_id === BALANCE_PAY && $sale_obj_type == 1 && $sale_obj_id){
                $res = db('stu_balance')->where('stu_id', '=', $sale_obj_id)->field('gift_balance, recharge_balance')->find();
                $stu_balance = $res['gift_balance'] + $res['recharge_balance'];
                $total = [];
                foreach ($goods_id as $k => $v) {  
                    $one_price = $single_price[$gk] * $sale_num[$gk];
                    $total[] = $one_price;
                }
                if($stu_balance < array_sum($total)){
                   $this->returnError(10001, '余额不足'); 
                }
            }

            foreach ($goods_id as $gk => $gval) {  
                $num = $sale_num[$gk];
                $sku_num = Db::name('goods_sku')->where('goods_id', '=', $gval)->value('sku_num');
                if ($sku_num < 1 ||  $sku_num < $num)
                {
                    $this->returnError(10001, '当中商品'.$gk.'库存不足');
                }
                
                $new_data = $comman_data;
                $new_data['goods_id'] = $gval;
                $new_data['sale_code'] = random_code();
                $new_data['sale_num'] = $num;
                $new_data['single_price'] = $single_price[$gk];
                $new_data['pay_amount'] = $new_data['sum_payable'] = $single_price[$gk] * $num;

                Db::name('goods_sale_log')->insert($new_data);
                $sku_num -= $num;
                Db::name('goods_sku')->where('goods_id', '=', $gval)->update(['sku_num' => $sku_num]);
                unset($new_data);
            }

            Db::commit();
            $this->returnData(1, '销售成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError(50000, '系统出错，销售失败' . $e->getMessage());
        } 
    }
    /*
     * 商品租凭
     */
    function rental()
    {
        $this->auth_get_token();
        $goods_id = input('goods_id/d', ''); // 商品id
        $rent_code = random_code();
        $rent_margin = input('rent_margin/f', 0.0); //租凭押金
        $rent_type = input('rent_type/d', 0);   // 租凭类型
        $rent_amount = input('rent_amount/f', ''); // 租凭金额
        $rent_num = input('rent_num/d', ''); // 租凭数量
        $prepaid_rent = input('prepaid_rent/f', ''); // 预付租金
        $rent_obj_type = input('rent_obj_type/d', ''); // 租凭对象类型
        $rent_obj_id = input('rent_obj_id/d', ''); // 租凭对象id
        $start_time = input('start_time/d', '');  // 租凭开始时间
        $end_time = input('end_time/d', '');  // 租凭结束时间
        $pay_id = input('pay_id/d', ''); // 支付方式id
        $remark = input('remark/s', ''); // 租凭备注
        $create_time = time();
        $update_time = time();
        if (is_empty($goods_id, $rent_type,
            $rent_amount, $rent_num, $prepaid_rent, $rent_obj_type,
            $start_time, $end_time, $end_time, $pay_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        if ($rent_margin < 0 || $rent_amount < 0 || $prepaid_rent < 0)
        {
            $this->returnError(10000, '金额不能小于0');
        }
        Db::startTrans();
        try
        {
            $sku_num = db('goods_sku')->where(['goods_id'=>$goods_id])->value('sku_num');
            if ($sku_num < 1 || $sku_num < $rent_num)
            {
                $this->returnError(10001, '库存不足');
            }
            $sku_num -= $rent_num;
            Db::name('goods_sku')->where('goods_id', '=', $goods_id)->
                update(['sku_num'=>$sku_num]);
            $record_data = [
                'goods_id' => $goods_id,
                'rent_code' => $rent_code,
                'rent_margin' => $rent_margin,
                'count_type' => $rent_type,
                'count_amount' => $rent_amount,
                'rent_num' => $rent_num,
                'prepay' => $prepaid_rent,
                'obj_type' => $rent_obj_type,
                'stu_id' => $rent_obj_id,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'remark' => $remark,
                'create_time' => $create_time,
                'update_time' => $update_time,
                'status' => 1,
                'manager' => ret_session_name('uid')
            ];
            $record_id = Db::name('goods_rent_record')->insertGetId($record_data);
            if($record_id){
                $log_data = [
                    'rent_margin' => $rent_margin,
                    'rent_amount' => $rent_amount,
                    'prepay' => $prepaid_rent,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'record_id' => $record_id,
                    'pay_id' => $pay_id,
                    'manager' => ret_session_name('uid'),
                    'update_time' => $update_time,
                    'remark' => $remark,
                    'status' => 1,
                    'manager' => ret_session_name('uid')
                ];
                db('goods_rent_log')->insert($log_data);
            }
            $this->returnData(1, '租赁成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError('50000', '租赁失败' . $e->getMessage());
        }
    }

}


