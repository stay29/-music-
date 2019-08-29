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


use Think\Exception;

class Records extends BaseController
{

    /*
     * 销售记录列表
     */
    public function sale_index()
    {
        /*
         * i.	商品名称
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
         */
        $goods_name = input('goods_name/s', '');
        $org_id = input('orgid/d', '');
        $page = input('page/d', 1);
        $limit = input('limit/d', 20);
        if (is_empty($org_id))
        {
            $this->returnError(10000, $org_id);
        }
        $db = db('goods_detail');
        if(!empty($goods_name))
        {
            $db->where('goods_name', 'like', '%' . $goods_name . '%');
        }

        $goods_list = $db->field('goods_id, goods_name, cate_id')->select();
        $response = [];
        foreach ($goods_list as $goods)
        {
            $goods_id = $goods['goods_id'];
            $cate_name = db('goods_cate')->where('cate_id', '=', $goods_id)->select();
            $sale_logs = db('goods_sale_log')->
                field('sale_id, sale_num, sale_code, sman_type, 
                sman_id, sale_obj_type, sale_obj_id, single_price, sum_payable,
                pay_amount, pay_id, remark, manager')->where('goods_id', '=', $goods_id)->select();
            foreach ($sale_logs as $log)
            {
                $sman_name = '';
                $sale_obj_name = '';
                if ($log['sman_type'] == 1) // 销售员
                {
                    $sman_name = db('salesmans')->where('sm_id', '=', $log['sman_id'])->value('sm_name');
                }elseif ($log['sman_type'] == 2)  // 老师
                {
                    $sman_name = db('salesmans')->where('t_id','=', $log['sman_id'])->value('t_name');
                }
                if ($log['sale_obj_type'] == 1)
                {
                    $sale_obj_name = db('students')->where('stu_id',
                        '=', $log['sale_obj_id'])->value('true_name');
                }else{
                    $sale_obj_name = '其他';
                }
                $manager = db('users')->where('uid', '=', $log['manager'])->value('nickname');
                $pay_type = db('payments')->where('pay_id', '=', $log['pay_id'])
                    ->value('payment_method');
                $response[] = [
                    'goods_name' => $goods['goods_name'],
                    'cate_name'  => $cate_name,
                    'sale_id'  => $log['sale_id'],
                    'sale_num'  => $log['sale_num'],
                    'sale_code' => $log['sale_code'],
                    'sman_name' => $sman_name,
                    'sale_obj_name' => $sale_obj_name,
                    'manager' => $manager,
                    'pay_type' => $pay_type,
                    'single_price' => $log['single_price'],
                    'sum_payable' => $log['sum_payable'],
                    'pay_amount' => $log['pay_amount'],
                    'remark' => $log['remark'],
                ];
            }

        }
        $this->returnData($response, '');
    }

    /*
      * 销售记录删除
       */
    public function sale_del()
    {
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
     * 租凭记录列表
     */
    public function rental_index()
    {

    }

    /*
     * 租借记录修改
     */
    public function rental_edit()
    {

    }

    /*
     * 添加租凭记录
     */
    public function rental_add()
    {

    }

    /*
     * 删除租凭记录
     */
    public function rental_del()
    {
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
     * 入库记录首页
     */
    public function storage_index()
    {

    }

    /*
     * 入库记录修改
     */
    public function storage_edit()
    {

    }

    /*
     * 入库记录删除
     */
    public function storage_del()
    {
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
     * 入库记录添加
     */
    public function storage_add()
    {

    }

    /*
     * 出库记录列表
     */
    public function checkout_index()
    {

    }

    /*
     * 出库记录修改
     */
    public function checkout_edit()
    {

    }

    /*
     * 出库记录删除
     */
    public function checkout_del()
    {
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
     * 出库记录添加
     */
    public function checkout_add()
    {

    }
}