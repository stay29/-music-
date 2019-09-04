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


class Seniorities extends BaseController
{
    /*
     * 查看资历
     */
    public function index()
    {
        $orgid = input('orgid', '');
        if (empty($orgid))
        {
            $this->return_data('0', '10000', '缺少参数');
        }
        $data = SenModel::where(['status'=>1, 'org_id'=>$orgid, 'is_del'=>0])
            ->field('seniority_id as s_id, seniority_name as s_name, is_official')
            ->order('sort')
            ->paginate(20);
        $this->return_data(1,'', '', $data);
    }

    /*
     * 添加资历
     */
    public function add()
    {
        $orgid = input('orgid', '');
        if (empty($orgid))
        {
            $this->return_data('0', '10000', '缺少参数');
        }
        $s_name = input('post.s_name/s', null);
        $order = input('post.order/int', 1);
        if(empty($s_name))
        {
            $this->return_data(1, '10000', '缺少参数');
        }
        $data = [
            'seniority_name' => $s_name,
            'sort' => $order,
            'is_del' => 0,
            'org_id' => $orgid
        ];
        try{
            SenModel::create($data);

            $this->return_data(1,'', '添加成功');
        }catch (Exception $e)
        {
            $this->return_data(0, '50000', '服务器错误');
        }
        $this->return_data(1, '', '添加资历成功','');
    }

    /*
     * 删除资历
     */
    public function del()
    {
        $s_id = input('post.s_id/d', '');
        $orgid = input('orgid/d', '');
        if (empty($orgid) || empty($s_id))
        {
            $this->return_data('0', '10000', '缺少参数');
        }
        try{
            $where[] = ['seniority_id', '=', $s_id];
            $where[] = ['is_del', '=', 0];
            $where[] = ['org_id', '=', $orgid];
            SenModel::where($where)->update(['is_del'=>1]);
            $this->return_data(1,'','删除成功');
        }catch (Exception $e)
        {
            $this->return_data(0, '50000', '系统错误');
        }
    }

    /*
     * 修改资历
     */
    public function edit()
    {
        $orgid = input('orgid', '');
        $s_name = input('post.s_name');
        $s_id = input('post.s_id');
        if(empty($s_name) || empty($s_id) || empty($orgid))
        {
            $this->return_data('0', '10000', '缺少必要参数');
        }
        try
        {
            $res = SenModel::where(['seniority_id'=>$s_id, 'is_del'=>1, 'org_id'=>$orgid])->update(
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