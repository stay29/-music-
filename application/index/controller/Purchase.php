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

class Purchase extends Controller
{

    public function get_cate($array, $pid=0, $level=0)
    {
        static $list = array();
        foreach ($array as $key=>$value)
        {
            if ($value['cate_pid'] == $pid)
            {
                $value['level'] = $level;
                $list[] = $value;
                unset($array[$key]);

                $this->get_cate($array, $value['cate_id'], $level+1);
            }
        }
        return $list;
    }

    /*
     * 分类列表
     */
    public function cate_index()
    {
        input('org_id');
        $categories = db('goods_cate')->field('cate_id, cate_pid, cate_name')
            ->order('order, create_time DESC')->select();
        $data = $this->get_cate($categories);
        return json_encode($data);
    }

    /*
     * 编辑分类
     */
    public function cate_edit()
    {

    }

    /*
     * 添加分类
     */
    public function cate_add()
    {

    }

    /*
     * 删除分类
     */
    public function cate_del()
    {

    }
}