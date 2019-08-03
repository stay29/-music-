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
        $orgid = ret_session_name('orgid');
        $uid =ret_session_name('uid');
        $data = [
            'nickname' =>input('post.nickname'),
            'cellphone' =>input('post.cellphone'),
            'password' =>input('post.password'),
            'rpassword' =>input('post.rpassword'),
            'organization' =>$orgid,
           // 'organization' =>input('post.organization'),
            'senfen'=>input('post.senfen'),
            'sex'=>input('post.sex'),
            'manager'=>$uid,
            'incumbency'=>input('incumbency'),
            'rid'=>input('rid'),
        ];
     $res =  add('erp2_users',$data,2);
     if($res){
         $this->return_data(1,0,'添加成功');
     }else{
         $this->return_data(0,10000,'添加失败');
     }
    }

    public function user_list()
    {
        //超级管理员
        $orgid = ret_session_name('orgid');
        $uid =ret_session_name('uid');
        $res =  Db::query("select * from erp2_users as u ,erp2_organizations as o where  u.uid=$uid AND u.organization=o.or_id ");
        $this->return_data(1,0,$res);
    }


    public  function  editincumbency()
    {
        $uid = input('uid');
        $incumbency = input('incumbency');
        $res = Db::query("UPDATE erp2_users SET incumbency=$incumbency WHERE uid=$uid");
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'操作失败');
        }
    }

    
    public  function  deluser()
    {
        $uid = input('uid');
        $res = Db::query("DELETE FROM erp2_users WHERE uid=$uid");
        if($res){
            $this->return_data(1,0,'操作成功');
        }else{
            $this->return_data(0,10000,'操作失败');
        }
    }







}