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
        'cellphone'=>'require|max:11|mobile|check_mobile_existed',
        'password'=>'require|length:5,15|check_user',
        'repassword'=>'require|confirm:password'
    ];
    protected $message = [
        'password.require'=>'密码不得为空|10000',
        'password.length'=>'密码长度不得小于5超过15|10001',
        'cellphone.require'=>'手机号不得为空|10000',
        'cellphone.mobile'=>'手机号格式不正确|10001',
        'cellphone.max'=>'手机号位数不正确|10001',
        'repassword.require'=>'确认密码不能为空|10000',
        'repassword.confirm'=>'两次密码不一致|10002',
    ];


    public function sceneAdd()
    {
         return $this->only(['cellphone','password','repassword'])
             ->remove('password','length|check_user');
    }

    public function sceneLogin()
    {
         return $this->only(['cellphone','password'])
             ->remove('cellphone','max|mobile|check_mobile_existed')
             ->remove('password','length');
    }

    protected function check_user($password,$rule,$data){
        $password = md5(md5(md5(MA.$password)));
        $user_info = Users::where(['cellphone'=>$data['cellphone'],'password'=>$password])->find();
        if($user_info){
            session(md5(MA.'user'),[
                'id'=>$user_info['uid'],
                'user_aco'=>$user_info['cellphone'],
                'username'=>$user_info['nickname'],
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



