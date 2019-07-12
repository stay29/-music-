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

class Login extends BaseController
{
    public function for_login()
    {

        $data = [
            'cellphone'=>input('post.user_aco'),
            'password'=>input('post.use_secret'),
        ];

        try{
            $validate = new \app\index\validate\User();
            if(!$validate->scene('login')->check($data)){
                $error = explode('|',$validate->getError());//为了可以得到错误码
                $this->return_data(0,$error[1],$error[0]);
            }else{
                $this->return_data(1,0,'登录成功');
            }
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }


    public function register_users()
    {
        $data = [
            'cellphone'=>input('post.cellphone'),
            'password'=>input('post.password'),
            'repassword'=>input('post.repassword'),
        ];
        try{
            $validate = new \app\index\validate\User();
            if(!$validate->scene('add')->check($data)){
                //为了可以得到错误码
                //dd($validate->getError());
                $error = explode('|',$validate->getError());
                $this->return_data(0,$error[1],$error[0]);
            }else{
                echo  1111;
            }
            $res = Users::addusers($data);
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

}