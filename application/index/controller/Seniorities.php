<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-7-25
 * Time: 下午2:24
 */

namespace app\index\controller;
use app\index\model\Seniorities as SenModel;
use think\Controller;
use think\Exception;


class Seniorities extends Controller
{
    /*
     * 查看资历
     */
    public function index()
    {
        $data = SenModel::where('status', '=', 1)
            ->field('seniority_id as s_id, seniority_name as s_name')
            ->order('sort')
            ->paginate(20);
        dump(json_encode($data));
    }

    /*
     * 添加资历
     */
    public function add()
    {
        $s_name = input('post.s_name/s', null);
        $order = input('post.order/int', 1);
        if(empty($s_name))
        {
            $this->return_data(1, '10000', '缺少参数');
        }
        $data = [
          'seniority_name' => $s_name,
            'sort' => $order
        ];
        try{
            $res = SenModel::create($data);
            if($res)
            {
                $this->return_data(1,'', '创建成功');
            }
            $this->return_data('0', '20001', '插入资历失败');
        }catch (Exception $e)
        {
            $this->return_data(0, '50000', '服务器错误');
        }
        $this->return_data();
    }

    /*
     * 删除资历
     */
    public function del()
    {
        $s_id = input('post.s_id/d');
        if(empty($s_id))
        {
            $this->return_data('0', '10000', '缺少必要参数');
        }
        try{
            $where = ['seniority_id', '=', $s_id];
            $res = SenModel::where($where)->delete();
            if($res)
            {
                $this->return_data(1,'','删除成功');
            }
            else
            {
                $this->return_data(0,'20003', '删除失败');
            }
        }catch (Exception $e)
        {
            $this->return_data(0, '50000', '系统错误');
        }
    }

    /*
     * 添加资历
     */
    public function edit()
    {
        $s_name = input('post.s_name');
        $s_id = input('post.s_id');
        if(empty($s_name) || empty($s_id))
        {
            $this->return_data('0', '10000', '缺少必要参数');
        }
        try
        {
            $res = SenModel::where('seniority_id', '=', $s_id)->update(
                ['seniority_name'=>$s_name]);
            if ($res)
            {
                $this->return_data(1, '', '修改成功');
            }
            $this->return_data('0', '', '修改失败');
        }catch (Exception $e)
        {
            $this->return_data(0, '50000', '系统错误');
        }
    }
}