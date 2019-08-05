<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/1
 * Time: 10:30
 */
namespace app\admin\controller;
use think\Collection;
use think\Db;
class Authinfo extends AdminBase
{
    public  function  authlist()
    {
        $title = '前台节点列表';
        $this->assign('add', url('admin/authinfo/addauth'));
        $this->assign('title', $title);
        $orgid = [];
        $res1 = selects('erp2_user_accesses',$orgid);
        $ar1 = ['顶级分类','模块','操作'];//可读性很强
        foreach ($res1 as $k=>&$v)
        {
            $v['type'] = $ar1[$v['type']];//可读性很强
            $v['status'] = $v['status']==1?'启用':'不启用';//可读性很强
            $a['access_id'] = $v['parent_id'];
            $v['parent_id'] = $v['parent_id']==0?'顶级分类':getname('erp2_user_accesses',$a,'access_name');
        }
        $this->assign('list', $res1);
        return view();
    }

    //添加节点
    public  function  addauth()
    {
        if ($this->request->isPost()) {
            $data = [
                //'orgid' =>input('orgid'),
                'access_name' =>input('access_name'),
                'access_url' =>input('access_url'),
                //'manager' =>input('uid'),
                'parent_id' =>input('parent_id'),
                'status'=>input('status'),
                'create_time'=>time(),
                'sort'=>input('sort'),
                'type'=>input('type'),
            ];
            // print_r($data);exit();
            $res = add('erp2_user_accesses',$data,2);
            if($res){
                $this->return_data(1, '操作成功');
            }else{
                $this->return_data(0, '操作失败');
            }
        }else{
            $title = '前台添加节点';
            $this->assign('title', $title);
            $o = input('orgid');
            $stat[] =  ['type','<>',2];
            $stat[] =  ['status','=',1];
            $res1 = Db::table('erp2_user_accesses')->where($stat)->select();
            $this->assign('authlist', $res1);
            return view();
        }
    }


    //修改节点
    public  function  eidtauth()
    {
        if ($this->request->isPost()) {
            $a['access_id'] = input('access_id');
            $data = input('');
            $resinfo =  edit('erp2_user_accesses',$a,$data);
            if($resinfo){
                $this->return_data(1, '操作成功');
            }else{
                $this->return_data(0, '操作失败');
            }
        }else{
            $title = '前台修改节点';
            $this->assign('title', $title);
            $a['access_id'] = input('access_id');
            $res = finds('erp2_user_accesses',$a);
            $this->assign('res', $res);
            //顶级节点
            $stat[] =  ['type','<>',2];
            $stat[] =  ['status','=',1];
            $res1 = Db::table('erp2_user_accesses')->where($stat)->select();
            $this->assign('authlist', $res1);
            return view();
        }
    }
    //删除应该没有人使用该节点
    public  function  delauth()
    {
        $a['access_id'] = input('access_id');
        try {
            $res = del('erp2_user_accesses',$a);
            $this->success('删除成功');
        } catch (Exception $e) {
            $this->error('删除失败');
        }
    }



}