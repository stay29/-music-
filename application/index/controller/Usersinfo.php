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
        $data = [
            'nickname' =>input('nickname'),
            'account'=>input('cellphone'),
            'cellphone' =>input('cellphone'),
            'password'=>$password,
            'organization' =>$orgid,
            'sex'=>input('sex'),
            'rid'=>implode(',',input('rid')),
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
            $error = explode('|',$validate->getError());//为了可以得到错误码
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
        $page = input('page');
        if($page==null){
            $page = 1;
        }
        $limit = input('limit');
        if($limit==null){
            $limit = 10;
        }
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
        foreach ($res as $k=>&$v){
            $v['orginfo'] = finds('erp2_organizations',['or_id'=>$v['organization']],'or_id,or_name');
            $v['ridinfo'] = $this->exp_name($v['rid'],'role_name');
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
        $page=(empty($page))?'1':$page; #判断当前页面是否为空 如果为空就表示为第一页面
        $start=($page-1)*$count; #计算每次分页的开始位置
        if($order==1){
            $array=array_reverse($array);
        }
        $pagedata=array();
        $pagedata['limit'] = $count;
        $pagedata['countarr'] = count($array);
        $pagedata['to_pages'] = ceil(count($array)/$count);
        $pagedata['page'] = $page;
        $pagedata['data']=array_slice($array,$start,$count);    //分隔数组
        return $pagedata;  #返回查询数据
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
        $uid = ret_session_name('uid');
        $list =  selects('erp2_user_roles',['is_del'=>0,'orgid'=>$orgid]);
        foreach ($list as $k=>&$v)
        {
            $v['info'] = $this->uuu($v['aid']);
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
        $uid = input('uid');
        $orgid = input('orgid');
        $pass = md5_return(input('pass'));
        $rpass =  md5_return(input('rpass'));
        $res = Db::query("select * from erp2_users  where uid=$uid AND organization=$orgid AND password=$pass");
        if($res){
            $info = Db::query("UPDATE erp2_users SET password=$rpass WHERE uid=$uid AND organization=$orgid");
            if($info){
                $this->return_data(1,0,'操作成功');
            }else{
                $this->return_data(0,10000,'没有任何改变');
            }
        }else{
         $this->return_data(0,10000,'原密码错误');
        }
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
        $data = [
            'nickname' =>input('nickname'),
            'cellphone' =>input('cellphone'),
            'organization' =>input('organization'),
            'sex'=>input('sex'),
            'rid'=>implode(',',input('rid')),
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
        $orgid = ret_session_name('orgid');
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
        $orlist = finds('erp2_organizations',['is_del'=>0,'status'=>2,'or_id'=>$orgid]);
        $orlist['f'] = "1";
        $res['auth'] = $a;
        $res['orglist'] = $orlist;
        $res['alist'] = $alist;
        $this->return_data(1,0,$res);
    }
}
