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
        'password'=>'require|check_user',
    ];


    protected $message = [
        'account.require'=>'账号不得为空|10000',
        'password.require'=>'密码不得为空|10000',
    ];

    protected $scene = [
//        'add' => ['account','password'],
//        'edit' => ['account','password'],
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
}



