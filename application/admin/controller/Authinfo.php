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
        $res1 = Db::table('erp2_user_accesses')->where($orgid)->paginate(15)->each(function ($v,$k){
            $ar1 = ['顶级分类','模块','操作'];//可读性很强
            $v['type'] = $ar1[$v['type']];//可读性很强
            $v['status'] = $v['status']==1?'启用':'不启用';//可读性很强
            $a['access_id'] = $v['pid'];
            $v['pid'] = $v['pid']==0?'顶级分类':getname('erp2_user_accesses',$a,'access_name');
            return $v;
        });
        $page = $res1->render();
        $this->assign('page', $page);
        $this->assign('list', $res1);
        return view();
    }

    public  function  addauth()
    {
        if ($this->request->isPost()) {
            $data = [
                //'orgid' =>input('orgid'),
                'access_name' =>input('access_name'),
                'a_home' =>input('a_home'),
                'a_coller' =>input('a_coller'),
                'a_action' =>input('a_action'),
                //'manager' =>input('uid'),
                'pid' =>input('pid'),
                'status'=>input('status'),
                'create_time'=>time(),
                'update_time'=>time(),
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


    public  function  eidtauth()
    {
        if ($this->request->isPost()) {
            $a['access_id'] = input('access_id');
            $data = input('');
            $data['update_time'] = time();
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


    public function  roleadd_list()
    {
        $title = '前台角色列表';
        $this->assign('title', $title);
        $this->assign('add', url('admin/authinfo/role_add_info'));
        $res = Db::table('erp2_user_roles')->where('status',1)->where('is_del',0)->paginate(15)->each(function ($v,$k){
            $a['or_id'] = $v['orgid'];
            $v['orgid'] = $v['orgid'] ==0?'系统创建':getname('erp2_organizations',$a,'or_name');
            $v['status'] = $v['status'] ==1?'正常':'禁用';
            return $v;
        });
        $this->assign('authlist', $res);
        return view();
    }

    public  function  role_add_info()
    {
        if ($this->request->isPost()) {
            $data = [
                'role_name' => input('role_name'),
                'status' => input('status'),
                'aid' => implode(',',input('aid')),
                'deflau' => input('deflau'),
                'orgid' => input('orgid'),
                'create_time'=>time(),
                'update_time'=>time(),
            ];
            $res = add('erp2_user_roles',$data);
            if($res){
                $this->return_data(1, '操作成功');
            }else{
                $this->return_data(0, '操作失败');
            }
        }else{
        $title = '前台添加角色';
        $this->assign('title', $title);
        $res = selects('erp2_user_accesses',['is_del'=>0,'type'=>0]);
        foreach ($res as $k=>&$v){
            $v['pid_type1'] = selects('erp2_user_accesses',['is_del'=>0,'type'=>1,'pid'=>$v['access_id']]);
            foreach ($v['pid_type1'] as $k1=>&$v1){
                $v1['pid_type2'] = selects('erp2_user_accesses',['is_del'=>0,'type'=>2,'pid'=>$v1['access_id']]);
            }
        }
        $this->assign('list', $res);
        $orglist = selects('erp2_organizations',['is_del'=>0]);
        $this->assign('orglist', $orglist);
        return view();
        }
    }


    public function  edit_role_linfo()
    {
        if ($this->request->isPost()) {
            $role_id['role_id'] = input('role_id');
            $data = [
                'role_name' => input('role_name'),
                'status' => input('status'),
                'aid' => implode(',',input('aid')),
                'deflau' => input('deflau'),
                'orgid' => input('orgid'),
            ];
        $edinfo = edit('erp2_user_roles',$role_id,$data);
            if($edinfo){
                $this->return_data(1, '操作成功');
            }else{
                $this->return_data(0, '操作失败');
            }
        }else{
            $title = '前台修改角色';
            $this->assign('title', $title);
            $res = selects('erp2_user_accesses',['is_del'=>0,'type'=>0]);
            foreach ($res as $k=>&$v){
                $v['pid_type1'] = selects('erp2_user_accesses',['is_del'=>0,'type'=>1,'pid'=>$v['access_id']]);
                foreach ($v['pid_type1'] as $k1=>&$v1){
                    $v1['pid_type2'] = selects('erp2_user_accesses',['is_del'=>0,'type'=>2,'pid'=>$v1['access_id']]);
                }
            }
            $this->assign('list', $res);
            $orglist = selects('erp2_organizations',['is_del'=>0]);
            $this->assign('orglist', $orglist);
            $role_id['role_id'] = input('role_id');
            $info = finds('erp2_user_roles',$role_id);
            $info['pidlist'] = explode(',',$info['aid']);
            $this->assign('info', $info);
            return view();
        }
    }
}