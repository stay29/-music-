<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/18
 * Time: 16:02
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use app\index\model\Users;
use app\index\model\Organization as Organ;
use app\index\controller\Organization;

class Usersinfo extends BaseController
{
    public  function  addusers(){
        $orgid = input('organization');
        $password =input('password');
        $rpassword =input('rpassword');
        if($password!=$rpassword){
            $this->return_data(0,10000,'两次密码不一致');
        }
        $u= finds('erp2_users',['organization'=>$orgid]);
        $uid = $u['uid'];
        if(input('rid')!=null){
            $datarid = implode(',',input('rid'));
        }else{
            $this->return_data(0,10000,'角色授权不能为空');
        }
        $data = [
            'nickname' =>input('nickname'),
            'account'=>input('cellphone'),
            'cellphone' =>input('cellphone'),
            'password'=>$password,
            'organization' =>$orgid,
            'sex'=>input('sex'),
            'rid'=>$datarid,
            'incumbency'=>1,
            'status'=>1,
            'create_time'=>time(),
            'update_time'=>time(),
            'senfen'=>$uid,
            'manager'=>$uid,
        ];
        Db::startTrans();
        try{
        $validate = new \app\index\validate\User();
        if(!$validate->scene('Addone')->check($data)){
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }else {
            $data['password'] = md5_return($data['password']);
            $res = add('erp2_users', $data, 2);
            Db::commit();
        }
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
     if($res){
         $this->return_data(1,0,'添加成功');
     }else{
         $this->return_data(0,10000,'添加失败');
     }
    }


    public function user_list()
    {
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);

        //超级管理员
        $uid = ret_session_name('uid');
        //$orgid['organization'] = input('orgid');
        if($uid!='181'){
            $orgid[] = ['organization','=', input('orgid')];
        }else{
            $orgid[] = ['organization','neq', ''];
        }
        $account = input('account');
        if($account){
                $orgid[] = ['account','like','%'.$account.'%'];
        }
        $orgid[] = ['is_del','=',"0"];
        $res = select_find('erp2_users',$orgid,'nickname,uid,cellphone,incumbency,rid,organization,sex,senfen');
        $aid = '';
        foreach ($res as $k=>&$v){
            $v['orginfo'] = finds('erp2_organizations',['or_id'=>$v['organization']],'or_id,or_name');
            $v['ridinfo'] = $this->exp_name($v['rid'],'role_name');
            $aid = $this->exp_name($v['rid'],'aid');
            $v['aid'] = $this->get_aid($aid);
        }
        $res_list = $this->array_page_list_show($limit,$page,$res,1);
        $this->return_data(1,0,'查询成功',$res_list);
    }

    public  function  exp_name($da,$name){
        $res = explode(',',$da);
        foreach ($res as $k=>&$v)
        {
            $aa[] = getname('erp2_user_roles',['role_id'=>$v],$name);
        }
        return implode(',',$aa);
    }

    public function array_page_list_show($count,$page,$array,$order)
    {
        $page=(empty($page))?'1':$page;
        $start=($page-1)*$count;
        if($order==1){
            $array=array_reverse($array);
        }
        $pagedata=array();
        $pagedata['limit'] = $count;
        $pagedata['countarr'] = count($array);
        $pagedata['to_pages'] = ceil(count($array)/$count);
        $pagedata['page'] = $page;
        $pagedata['data']=array_slice($array,$start,$count);
        return $pagedata;
    }

    public  function  getrole_user()
    {
        $page = input('page');
        if($page==null){
            $page = 1;
        }
        $limit = input('limit');
        if($limit==null){
            $limit = 10;
        }
        $orgid = ret_session_name('orgid');
        $uid =   ret_session_name('uid');
        $list =  selects('erp2_user_roles',['is_del'=>0,'orgid'=>$orgid]);
        foreach ($list as $k=>&$v)
        {
            $v['info'] = $this->uuu($v['aid']);
            $v['get_aid'] = $this->get_aid($v['aid']);
        }
        $res = $this->array_page_list_show($limit,$page,$list,1);
        $this->return_data(1,0,$res);
    }

    public  function  uuu($aid)
    {
        $aidlist = explode(',',$aid);
        foreach ($aidlist as $k=>&$v){
            $arr = finds('erp2_user_accesses',['access_id'=>$v]);
            $arr['pidinfo'] = selects('erp2_user_accesses',['pid'=>$v]);
            $array[] = $arr;
        }
        return $array;
    }

    public  function  get_aid($aid)
    {
        if($aid!=null){
             $arr = explode(',',trim($aid,','));
             $arr1 = $this->a_array_unique($arr);
             $acclist = Db::table('erp2_user_accesses')->field('access_id')->where('access_id','in',$arr1)->where('type',1)->select();
             $acclist1 = [];
             foreach ($acclist as $k=>$v){
                 $acclist1[] = $v['access_id'];
             }
             //获取全部节点
             $acclist3 = Db::table('erp2_user_accesses')->field('access_id')->where('type',0)->select();
             foreach ($acclist3 as $k3=>&$v3)
             {
                 $type3 = Db::table('erp2_user_accesses')->field('access_id')->where('pid',$v3['access_id'])->where('type',1)->select();
                 $type_list3 = [];
                 foreach ($type3 as $k4=>$v4){
                     $type_list3[] = $v4['access_id'];
                 }
                 $v3['type3'] = $type_list3;
             }
             $f1 = [];
             foreach ($acclist3 as $k5=>&$v5)
             {
                 if($v5['type3']==null){
                    $f1[] = $v5['access_id'];
                 }else{
                     foreach ($v5['type3'] as $k6=>$v6)
                     {
                        if(!in_array($v6['access_id'],$arr)){
                            $f1[] = $v5['access_id'];
                            break;
                        }
                     }
                 }
             }
            foreach ($arr as $k7=>&$v7)
            {
                foreach ($f1 as $k8=>&$v8)
                {
                    if($v7==$v8){
                        unset($arr[$k7]);
                    }
                }
            }
            return implode(',',$arr);
        }
    }

    function a_array_unique($array)
    {
        $out = array();
        foreach ($array as $key=>$value) {
            if (!in_array($value, $out))
            {
                $out[$key] = $value;
            }
        }
        return $out;
    }



    public  function  editincumbency()
    {
        $uid['uid'] = input('uid');
        $incumbency['incumbency'] = input('incumbency');
        $res = edit('erp2_users',$uid,$incumbency);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'操作失败');
        }
    }


    public  function  deluser()
    {
        $uid['uid'] = input('uid');
        $data['is_del'] = 1;
        $res = edit('erp2_users',$uid,$data);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'操作失败');
        }
    }

    public function  editpass()
    {
//        //print_r(input('post.'));exit();
//        $uid = input('uid');
//        $orgid = input('orgid');
//        $where['uid'] = $uid;
//        $data['password'] =  md5_return(input('pass'));
//         $res = edit('erp2_users',$where,$data);
//         if($res){
//             $this->return_data(1,0,'操作成功');
//         }else{
//             $this->return_data(0,10000,'操作失败');
//         }
        $new_pwd = input('pass/s', '');
        $raw_pwd = input('rpass/s', '');
//        $uid = input('uid', '');
//        $org_id = input('orgid', '');
        $mobile = input('cellphone', '');
        if ($new_pwd != $raw_pwd)
        {
            $this->return_data(0, '10000', '两次密码不能一致');
        }

        $new_pwd = md5_return($new_pwd);
        $db_raw_pwd = db('users')->where(['account'=>$mobile])->value('password');

        if ($db_raw_pwd == $new_pwd)
        {
            $this->return_data(0, '10000', '新旧密码不能一致');
        }
        db('users')->where(['account' => $mobile])->update(['password' => $new_pwd]);
        $this->return_data(1, '10000', '修改成功');
    }


    public  function  getoneuser()
    {
        $uid = input('uid');
        $orgid = input('orgid');
        $mup['uid'] = $uid;
        $mup['organization'] = $orgid;
        $res = select_find('erp2_users',$mup,'uid,account,nickname,cellphone,sex,organization,rid,incumbency');
        $res['orginfo'] = select_find('erp2_organizations',['or_id'=>$orgid],'or_id,or_name,status,contact_man');
        $res['ridlist'] =explode(',',$res['rid']);
        //$res['ridinfo'][] = selects('erp2_user_roles',);
        $this->return_data(1,0,'查询成功',$res);
    }

    public function  edituser_info()
    {
        $uid = input('uid');
        if(input('rid')==null){
            $this->return_data(0,10000,'请选择授权角色');
        }else{
            $rid = implode(',',input('rid'));
        }
        $c = Db::table('erp2_users')->where('cellphone',input('cellphone'))->find();
        if($c['uid']!=$uid){
            if($c){
                $this->return_data(0,10000,'该账户已被注册 请联系管理员');
            }
        }
        if(input('organization')==null)
        {
            $this->return_data(0,10000,'请选择机构');
        }
        $data = [
            'nickname' =>input('nickname'),
            'cellphone' =>input('cellphone'),
            'organization' =>input('organization'),
            'sex'=>input('sex'),
            'rid'=>$rid,
            'update_time'=>time(),
        ];
        $res = Db::table('erp2_users',null)->where('uid',$uid)->update($data);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'没有任何改变');
        }
    }


    public  function  addrole_new()
    {
        $rid = input('rid');
        $uid = input('uid');
        $res = edit('erp2_users',['uid'=>$uid],['rid'=>$rid]);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'没有任何改变');
        }
    }

    /*
     *
     */
    public  function  add_accauth_list()
    {
        $data = [
            'role_name' =>input('role_name'),
            'status' =>1,
            'manager' =>input('uid'),
            'orgid' =>input('orgid'),
            'aid' =>implode(',',input('aid')),
            'remake'=>input('remake'),
            //'aid' =>implode(',',['1','3']),
            'deflau' =>2,
            'create_time'=>time(),
            'update_time'=>time(),
        ];
        $res = add('erp2_user_roles',$data);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'没有任何改变');
        }
    }


    public  function  edit_accauth_list()
    {
        $rid['role_id'] = input('rid');
        $data = [
            'role_name' =>input('role_name'),
            'status' =>1,
            'manager' =>input('uid'),
            'orgid' =>input('orgid'),
            'aid' =>implode(',',input('aid')),
            'remake'=>input('remake'),
            'deflau' =>2,
        ];
        $res = edit('erp2_user_roles',$rid,$data);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'没有任何改变');
        }
    }


    public function  del_accauth_info()
    {
        $rid['role_id'] = input('rid');

        $res = db('users')->where('rid', 'like', $rid)->count();
        if ($res)
        {
            $this->return_data(0, '20003', '角色被使用,无法删除');
        }
        $data = [
            'is_del'=>1,
        ];
        $res = edit('erp2_user_roles',$rid,$data);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'没有任何改变');
        }
    }

    public function get_auth_orgid_list()
    {
        $orgid = input('post.orgid');
        $list1 = selects('erp2_user_roles',['is_del'=>0,'deflau'=>1]);
        $list =  selects('erp2_user_roles',['is_del'=>0,'orgid'=>$orgid,'deflau'=>2]);
        $a = array_merge($list1,$list);
        //print_r($a);exit();
        foreach ($a as $k=>&$v)
        {
            $v['f'] = "1";
        }
        $alist = selects('erp2_user_accesses',['is_del'=>0,'type'=>0]);
        foreach ($alist as $k1=>&$v1)
        {
            $v['f'] = "1";
            $v1['pidlist'] = selects('erp2_user_accesses',['is_del'=>0,'type'=>1,'pid'=>$v1['access_id']]);
        }

        $orlist =Organization::get_org_list_m($orgid);
        foreach ($orlist as $k2=>&$v2)
        {
            $v2['f'] = "1";
        }
//        $orlist['f'] = "1";
        $res['auth'] = $a;
        $res['orglist'] = $orlist;
        $res['alist'] = $alist;
        $this->return_data(1,0,"查询成功",$res);
    }


}
