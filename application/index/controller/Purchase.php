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

class Categories
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
                $value['level'] = $level;
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


class Purchase extends Controller
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
        if(empty($cate_name) || empty($org_id))
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
        if(empty($cate_name) || empty($cate_id) || empty($cate_pid) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        Db::startTrans();
        try
        {
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
        if (empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        $categories = db('goods_cate')->field('cate_id, cate_pid, cate_name')
            ->order('order, create_time DESC')->where('org_id', '=', $org_id)->select();
        $data = Categories::getSelectCate($categories);
        $this->return_data(1, '', '请求成功', $data);

    }


    /*
     * 删除分类
     */
    public function cate_del()
    {
        $cate_id = input('cate_id/d', '');
        $org_id = input('orgid/d', '');
        if(empty($cate_id))
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

}