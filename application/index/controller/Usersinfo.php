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
            'organization' =>input('organization'),
            'sex'=>input('sex'),
            'rid'=>input('rid'),
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
        $orgid['organization'] = input('orgid');
        $orgid['is_del'] = 0;
        $res = select_find('erp2_users',$orgid,'nickname,uid,cellphone,incumbency,rid,organization,sex,senfen');
        foreach ($res as $k=>&$v){
            $v['orginfo'] = finds('erp2_organizations',['or_id'=>$v['organization']],'or_id,or_name');
            $v['ridinfo'] = $this->exp_name($v['rid'],'role_name');
        }

        $res_list = $this->array_page_list_show($limit,$page,$res,1);
        $this->return_data(1,0,'查询成功',$res);
    }

    public  function  exp_name($da,$name){
        $res = explode(',',$da);
        foreach ($res as $k=>&$v)
        {
            $aa[] = getname('erp2_user_roles',['role_id'=>$v],$name);
        }
        return implode(',',$aa);
    }

    //数组分页方法
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
            'rid'=>input('rid'),
            'update_time'=>time(),
        ];
        $res = Db::table('erp2_users',null)->where('uid',$uid)->update($data);
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'没有任何改变');
        }
    }

}