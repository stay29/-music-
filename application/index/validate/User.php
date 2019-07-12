<?php
/**
 * 用户，校长表
 * User: antony
 * Date: 2019/7/11
 * Time: 10:23
 */

namespace app\index\validate;


use app\index\model\Users;
use think\Validate;

class User extends Validate
{
    protected $rule = [
        'account'=>'require',
        'cellphone'=>'require|max:11|mobile|check_mobile_existed',
        'password'=>'require|length:5,15|check_user',
        'repassword'=>'require|confirm:password'
    ];
    protected $message = [
        'account.require'=>'账号不得为空|10000',
        'password.require'=>'密码不得为空|10000',
        'password.length'=>'密码长度不得小于5超过15|10001',
        'cellphone.require'=>'手机号不得为空|10000',
        'cellphone.mobile'=>'手机号格式不正确|10001',
        'cellphone.max'=>'手机号位数不正确|10001',
    ];
    protected $scene = [
        'add' => ['cellphone','password'=>'require|length'],
        //'edit' => ['account','password'],
        'login' => ['account','password'],
    ];

    protected function check_user($password,$rule,$data){
        $password = md5(md5(md5(MA.$password)));
        $user_info = Users::where(['account'=>$data['account'],'password'=>$password])->find();
        if($user_info){
            session(md5(MA.'user'),[
                'id'=>$user_info['uid'],
                'user_aco'=>$user_info['account'],
                'username'=>$user_info['nickname'],
                'mobile'=>$user_info['cellphone'],
                'sex'=>$user_info['sex'],
            ]);
            return true;
        }else{
            return '用户名密码错误|20007';
        }
    }
    protected function check_mobile_existed($cellphone)
    {
        $info = Users::where('cellphone',$cellphone)->find();
        if($info){
            return '手机号已被注册|20009';
        }else{
            return true;
        }
    }

}



