<?php
/**
 * 进销存模块
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-14
 * Time: 下午4:50
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Exception;

use app\index\model\Goods as GoodsModel;
use app\index\validate\Goods as GoodsValidate;


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

    /*
     * 返回添加或修改分类时的分类下拉框信息
     */
    static public function getSelectCate($items)
    {
        foreach ($items as $item)

            $items[$item['cate_pid']]['son'][$item['cate_id']] = &$items[$item['cate_id']];

        return isset($items[0]['son']) ? $items[0]['son'] : array();
    }

}


class Purchase extends BaseController
{
    /*
     * 分类列表
     */
    public function cate_index()
    {
        $org_id = input('orgid/d', '');
        if(empty($org_id))
        {
            $this->return_data(0,'1000', '缺少参数', false);
        }
        $categories = db('goods_cate')->field('cate_id, cate_pid, cate_name')
            ->order('order, create_time DESC')->where('org_id', '=', $org_id)->select();
        $data = Categories::getIndexCate($categories);
        $this->return_data(1, '', '请求成功', $data);
    }

    /*
     * 添加分类
     */
    public function cate_add()
    {
        $org_id = input('orgid/d', '');
        $cate_pid = input('cate_pid', 0);
        $cate_name = input('cate_name', '');
        $order = input('order', 0);
        if(is_empty($org_id, $cate_name))
        {
            $this->return_data(0, '', '缺少参数', false);
        }
        if(strlen($cate_name) > 20)
        {
            $this->return_data('0', '10000', '分类名称字符过长', false);
        }
        if ($cate_pid != 0)
        {
            $cate = Db::name('goods_cate')
                ->field('cate_name')->where(['cate_id' => $cate_pid, 'org_id'=>$org_id])->find();
            if(empty($cate))
            {
                $this->return_data(0, '20001', '添加失败, 父级分类不存在', false);
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
            $this->return_data(1, '', '添加成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '20001', '添加失败', false);
            Db::rollback();
        }
    }

    /*
     * 修改分类
     */
    public function cate_edit()
    {
        $cate_id = input('cate_id/d', '');
        $cate_name = input('cate_name/s', '');
        $order = input('order/d', 0);
        $cate_pid = input('cate_pid/d', '');
        $org_id = input('orgid/d', '');
        if(is_empty($cate_pid, $org_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        Db::startTrans();
        try
        {
            $p_cate = db('goods_cate')->where('cate_id', '=', $cate_pid)->select();
            if (empty($p_cate) && $cate_pid != 0)
            {
                $this->return_date(0, '10000', '父级分类不存在', false);
            }
            $data = [
                'cate_name' => $cate_name,
                'cate_pid'  => $cate_pid,
                'org_id'    => $org_id,
                'order' => $order
            ];
            Db::where(['org_id'=>$org_id, 'cate_id'=>$cate_id])->update($data);
            Db::commit();
            $this->return_data(1, '', '修改成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '20002', '修改失败');
        }
    }

    /**
     * 添加分类或修改分类时的下拉显示列表
     */
    public function cate_list()
    {
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        $categories = db('goods_cate')->field('cate_id, cate_pid, cate_name')
            ->order('order, create_time DESC')->where('org_id', '=', $org_id)->select();
        $data = Categories::getSelectCate($categories);
        $this->return_data(1, '', '请求成功', $data);

    }


    /**
     * 删除分类
     */
    public function cate_del()
    {
        $cate_id = input('cate_id/d', '');
        $org_id = input('orgid/d', '');
        if(is_empty($cate_id, $org_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        $son_cate = Db::name('goods_cate')->where(['org_id'=>$org_id, 'cate_pid'=>$cate_id])->select();
        if (!empty($son_cate))
        {
            $this->return_data(0, '10000', '请先删除子分类', false);
        }
        Db::startTrans();
        try{
            Db::name('goods_cate')->where(['cate_id'=>$cate_id, 'org_id'=>$org_id])->delete();
            Db::commit();
            $this->return_data(1, '', '删除成功', true);
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data(0, '20003', '删除失败', false);
        }
    }

    /*
     * 销售员列表
     */
    public function sale_mans_index()
    {
        $org_id = input('orgid/d', '');
        if (is_empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数', '');
        }
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        $data = db('salesmans')->where('org_id', '=', $org_id)
            ->field('sm_id, sm_name, sm_mobile, status')
            ->paginate($limit);
        $this->return_data(1, '', '请求成功', $data);
    }

    /*
     * 删除销售员
     */
    public function sale_mans_del()
    {
        $sm_id = input('sm_id/d', '');
        if(is_empty($sm_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        db('salesmans')->where('sm_id', '=', $sm_id)->delete();
        $this->return_data('0', '', '删除成功', true);
    }

    /*
     * 修改销售员
     */
    public function sale_mans_edit()
    {
        $data = [
            'sm_id' => input('sm_id/d', ''),
            'sm_name' => input('sm_name/s', ''),
            'org_id' => input('org_id/d', ''),
            'sm_mobile' => input('sm_mobile', ''),
            'status' => input('status')
        ];
        if (is_empty($data['sm_id'], $data['org_id'], $data['sm_mobile']))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        try{
            $data['update_time'] = time();
            db('salesmans')->update($data);
            $this->return_data(1, '', '修改成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '20002', '系统错误, 修改失败', false);
        }
    }

    /*
     * 添加销售员
     */
    public function sale_mans_add()
    {
        $data = [
            'sm_id' => input('sm_id/d', ''),
            'sm_name' => input('sm_name/s', ''),
            'org_id' => input('org_id/d', ''),
            'sm_mobile' => input('sm_mobile', ''),
            'status' => input('status')
        ];
        foreach ($data as $k => $v)
        {
            if (empty($v))
            {
                $this->return_data(0, '10000', '缺少参数:'. $k, false);
            }
        }
        try{
            $data['create_time'] = time();
            $data['update_time'] = time();
            db('salesmans')->insert($data);
            $this->return_data(1, '', '修改成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '20002', '系统错误, 修改失败', false);
        }
    }

    /*
     * 销售员离职
     */
    public function sale_mans_departure()
    {
        $sm_id = input('sm_id/d', '');
        if(empty($sm_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        try
        {
            db('salesmans')->where('sm_id', '=', $sm_id)->update(['status'=>2]);
            $this->return_data(1, '', '离职成功', true);
        }catch (Exception $e)
        {
            log($e->getMessage());
            $this->return_data(0, '50000', '离职失败', false);
        }
    }

    /*
     * 销售员复职
     */
    public function sale_mans_recovery()
    {
        $sm_id = input('sm_id/d', '');
        if(empty($sm_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        try{
            db('salesmans')->where('sm_id', '=', $sm_id)->update(['status'=>1]);
            $this->return_data(1, '', '复职成功', false);
        }catch (Exception $e)
        {
            log($e->getMessage());
            $this->return_data(0, '50000', '离职失败', false);
        }
    }
    /*
     * 商品列表
     */
    public function goods_index()
    {

    }

    /*
     * 添加商品
     */
    public function goods_add()
    {
        $data = input('post');
        try{
            $validate = new GoodsValidate();
            if (!$validate->check($data))
            {
                $error = explode('|', $validate->getError());
                $this->return_data(0, $error[0], $error[1]);
            }
            $goods = new GoodsModel($data);
            $goods->save();
        }catch (Exception $e)
        {
            $this->return_data(0, '50000', '系统错误');
        }
    }

    /*
     * 删除商品
     */
    public function goods_del()
    {
        $goods_id = input('goods_id', '');
        if(empty($goods_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        Db::startTrans();
        try
        {
            // 删除产品和库存数据
            Db::name('goods_detail')->where('goods_id', '=', $goods_id)->delete();
            Db::name('goods_sku')->where('goods_id', '=', $goods_id)->delete();
            Db::commit();
            $this->return_data(1, '', '删除成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data(0, '50000', '系统错误');
        }
    }

    /*
     * 修改商品
     */
    public function goods_edit()
    {
        $data = input('post');
        try{
            $validate = new GoodsValidate();
            if(!$validate->check($data))
            {
                $error = explode('|', $validate->getError());
                $this->return_data(0, $error[0], $error[1]);
            }
            GoodsModel::update($data);
            $this->return_data(1, '', '修改成功');
        }catch (Exception $e)
        {
            $this->return_data(0, '50000', '系统出错');
        }
    }

    /*
     * 商品入库
     */
    public function goods_storage()
    {
        $goods_id = input('goods_id/d', '');
        $goods_num = input('goods_num/d', '');
        $goods_price = input('goods_price/f', '');
        $remark = input('remark/s', '');
        $entry_time = input('entry_time/d', time());
        if(is_empty($goods_id, $goods_num, $goods_price))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        if ($goods_num <= 0 || $goods_price <= 0)
        {
            $this->return_data(0, '10000', '商品数量和价格必须大于0');
        }
        if (strlen($goods_remark) > 200)
        {
            $this->return_data(0, '10000', '备注不能超过200字符');
        }
        Db::startTrans();
        try
        {
            $goods_id = Db::name('goods_detail')->where('goods_id', '=', $goods_id)->value('goods_id');
            if (empty($goods_id))
            {
                $this->return_data(0, '10000', '20001', '入库失败');
            }
            $sku = Db::name('goods_sku')->where('goods_id', '=', $goods_id)->find();
            if (!empty($sku))
            {
                $sku_id = $sku['sku_id'];
                $sku_num = $sku['sku_num'] + $goods_num;
                Db::name('goods_sku')->where('sku_id', '=', $sku_id)->update(['sku_num' => $sku_num]);
            }
            $sku_data = [
                ['goods_id', '=', $goods_id],
                ['sku_num', '=', $goods_num]
            ];
            Db::name('goods_sku')->insert($sku_data);

            $sto_data = [
                'goods_id' => $goods_id,
                'sto_num' => $goods_num,
                'sto_single_price' => $goods_price,
                'remark'    => $remark,
                'entry_time' => $entry_time,
                'create_time' => time(),
                'update_time' => time(),
                'sto_code'  => random_code()
            ];
            Db::name('goods_storage')->insert($sto_data);
            Db::commit();
            $this->return_data(1, '', '', '入库成功');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data(1, '', '', '入库失败');
        }
    }

    /*
     * 商品出库
     */
    public function goods_checkout()
    {
        $goods_id = input('goods_id/d', '');
        $dep_num = input('dep_num/d', '');
        $dep_price = input('dep_price', '');
        $dep_time = input('dep_time/d', time());
        $remarks = input('remarks/s', '');
        if (is_empty($goods_id, $dep_num, $dep_price))
        {
            $this->return_data(0, '10000', '缺少参数', '');
        }
        if (strlen($remarks) > 200)
        {
            $this->return_data(0, '10000', '备注200字符内');
        }
        if ($dep_num <= 0 || $dep_price <= 0 )
        {
            $this->return_data(0, '10000', '商品价格和数量不能小于0');
        }
        Db::startTrans();
        try
        {
            // 校验库存
            $sku_num = Db::name('goods_sku')->where('goods_id', '=', $goods_id)->value('sku_num');
            if ($sku_num < $dep_num)
            {
                $this->return_data(1, '20001', '出库失败,库存不足');
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
                'dep_code'  => random_code()
            ];
            // 出库记录
            Db::name('goods_deposit')->insert($dep_data);
            Db::commit();
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data(0, '50000', '系统出错, 出库失败');
        }
    }

    /*
     * 商品销售
     */
    public function goods_sale()
    {
        $goods_id = input('goods_id/d', '');
        $sman_type = input('sman_type/d', ''); //销售员类型, 1销售员,2老师
        $sman_id = input('sman_id/d', ''); // 销售员id或教师id
        $sale_obj_type = input('sale_obj_type', ''); // 销售对象,1学生2其他
        $sale_obj_id = input('sale_obj_id', ''); // 学生id, 或其他则为0
        $pay_id = input('pay_id/d', ''); // 支付方式
        $remark = input('remark/s', ''); // 备注
        $sale_time = input('sale_time/d', time()); // 销售时间
        $create_time = time();
        $update_time = time();
        if (is_empty($goods_id, $sman_type, $sman_id, $sale_obj_type, $sale_obj_id, $pay_id))
        {
            $this->return_data(0, '10000', '缺少必填参数');
        }
        Db::startTrans();
        try
        {
            $sale_data = [];



            Db::name('goods');
        }catch (Exception $e)
        {
            Db::rollback();
            $this->return_data(0, '10000', '系统出错，销售失败');
        }
    }
}


