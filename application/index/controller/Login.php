<?php
/**
 * 登录
 * User: antony
 * Date: 2019/7/11
 * Time: 15:43
 */

namespace app\index\controller;
use app\index\model\Users;
use app\index\validate\User;
use think\facade\Session;

class Login extends BaseController{
    public function for_login(){
        $data = [
            'cellphone'=>input('post.user_aco'),
            'password'=>input('post.use_secret'),
            'remember'=>input('post.remember')
        ];
        try{
            $validate = new \app\index\validate\User();
            if(!$validate->scene('login')->check($data)){
                $error = explode('|',$validate->getError());//为了可以得到错误码
                $this->return_data(0,$error[1],$error[0]);
            }else{
                $this->return_data(1,0,'登录成功',session(md5(MA.'user')));
            }
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    public function register_users()
    {
        $data = [
            'cellphone'=>input('post.user_aco'),
            'password'=>input('post.use_secret'),
            'repassword'=>input('post.use_secret_repassword'),
        ];
        $vieryie = input('post.vieryie');
        if($vieryie !=Session::get('vieryie')){
            $this->return_data(0,0,'验证码不一致');
        }
        try{
            $validate = new \app\index\validate\User();
            if(!$validate->scene('add')->check($data)){
                //为了可以得到错误码
                $error = explode('|',$validate->getError());
                $this->return_data(0,$error[1],$error[0]);
            }else{
            $mup['account']   = $data['cellphone'];
            $mup['cellphone'] = $data['cellphone'];
            $mup['password']  = md5_return($data['password']);
            $res = Users::addusers($mup);
            session(null);
            $this->return_data(1,0,'注册成功');
            }
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    public  function  aaa(){
        $a = 'abc123';
        $res = md5_return($a);
        return $res;
    }

    public  function  logout()
    {
        session(null);
        $this->return_data(1,0,'退出登录');
    }

     //验证码获取
    public  function  get_vieryie(){
        $len = 4;
        $chars = array(
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = "";
        for ($i=0; $i<$len; $i++)
        {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        session(null);
        Session::set('vieryie',$output);
        //return $output;
        $this->return_data(1,0,$output);
    }

}