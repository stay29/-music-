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


class Records extends BaseController
{

    /*
     * 销售记录列表
     */
    public function sale_index()
    {
        $goods_name = input('goods_name/s', '');
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->returnError(10000, $org_id);
        }
        $db = db('goods_detail');
        if(!empty($goods_name))
        {
            $db->where('goods_name', 'like', '%' . $goods_name . '%');
        }
        $goods_id_list = $db->field('goods_id, goods_name')->select();

        foreach ($goods_id_list as $goods_id)
        {

        }


    }
    /*
     * 租凭记录列表
     */
    public function rental_index()
    {

    }

    /*
     * 租借记录删除
     */
    public function rental_del()
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

    }

    /*
     * 出库记录添加
     */
    public function checkout_add()
    {

    }
}