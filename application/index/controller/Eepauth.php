<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/1
 * Time: 14:50
 */
namespace app\index\controller;
use think\Controller;
use think\Db;

class Eepauth extends BaseController
{
    public function  auth_list()
    {
        $a['status'] = 1;
        $a['parent_id'] = 0;
        $a['type'] = 0;
        $res = selects('erp2_user_accesses',$a);
        foreach ($res as $k=>&$v)
        {
            $b['parent_id'] = $v['access_id'];
            $b['type'] = 1;
            $b['status'] = 1;
            $v['pid'] = selects('erp2_user_accesses',$b);
         foreach ($v['pid'] as $ks=>&$vs)
         {
             $c['parent_id'] = $vs['access_id'];
             $vs['pidinfo'] = selects('erp2_user_accesses',$c);
         }
        }
        $this->return_data(1,0,'全部节点',$res);
    }


    //添加角色
    public  function  add_roles()
    {
         $uid = input('uid');
         $orgid = input('orgid');
         //$pid = input('pid');
         $pid = ['6','10','19'];
        $data = [
             'role_name' =>input('role_name'),
             'status'=>1,
             'manager'=>$uid,
             'orgid'=>$orgid,
             'create_time'=>time(),
         ];
         $res = add('erp2_user_roles',$data,2);
         if($res){
            foreach ($pid as $k=>$v)
            {
                    $data2['role_id'] = $res;
                    $data2['aid'] = $v;
                    $a['access_id']= $v;
                    $b = getname('erp2_user_accesses',$a,'type');
                    $data2['atype'] = $b;
                    $data2['uid'] = $uid;
                    $data2['orgid'] = $orgid;
                    $info = add('erp2_user_role_relations',$data2,2);
            }
            if($info){
                $this->return_data(1,0,'操作成功');
            }else{
                 $this->return_data(0,10000,'操作失败');
             }
         }else{
             $this->return_data(0,10000,'操作失败');
         }
    }


    public function  get_role_info()
    {
        $uid = input('uid');
        $where['uid'] = $uid;
        $where['organization'] = input('orgid');
        $res = finds('erp2_users',$where);
        $a['role_id'] = $res['rid'];
        $res['roleinfo'] = finds('erp2_user_roles',$a);
        $b['role_id'] = $res['rid'];
        $b['orgid'] =input('orgid');
        $authlist = selects('erp2_user_role_relations',$b);
        foreach ($authlist as $k=>&$v){
            $c['access_id'] = $v['aid'];
            $v['aidinfo'] = finds('erp2_user_accesses',$c);
        }
        $res['authlist'] = $authlist;
        $this->return_data(1,0,$res);
    }




}



