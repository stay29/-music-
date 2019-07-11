<?php
/**
 * 登录
 * User: antony
 * Date: 2019/7/11
 * Time: 15:43
 */

namespace app\index\controller;


class Login extends BaseController
{
    public function for_login(){

        $data = [
            'account'=>input('post.user_aco'),
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
}