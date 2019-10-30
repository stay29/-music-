<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/23
 * Time: 13:43
 */

namespace app\index\controller;
use think\Request;
use Firebase\JWT\JWT;//引入验证类
use think\Controller;

class Jwttoken  extends Basess
{
    //获取token
    public function createJwt($data)
    {
        $key = md5('nobita'); //jwt的签发密钥，验证token的时候需要用到
        $time = time(); //签发时间
        $expire = $time + 72000; //过期时间
        $token = array(
            //"iss" => "https://199508.com",//签发组织
            //"aud" => "https://199508.com", //签发作者
            "iat" => $time,//签发时间
            "nbf" => $time,//什么时间当前token可以用
            "exp" => $expire,//过期时间
            "data" =>$data
        );
        $jwt = JWT::encode($token, $key);
        return $jwt;
    }
    //解密token
    public function check_token($jwt){
        $key = md5('nobita'); //jwt的签发密钥，验证token的时候需要用到
        $info = JWT::decode($jwt,$key,["HS256"]); //解密jwt
        $arr =  json_decode(json_encode($decoded), true);
        return $arr;
    }

    //验证token的真实性
    public  function  checj_token_time($jwt)
    {
        $key = md5('nobita');
        JWT::$leeway = 60;//过期时间验证
        $decoded = JWT::decode($jwt, $key, ['HS256']);
        $arr =  json_decode(json_encode($decoded), true);
        $time = time();
        if(!$arr){
        $this->return_data(0,10000,'token验证失败');
        }else{
            if($arr['exp']>$time){
        $this->return_data(0,10000,'token已经过期');
            }else{
        $this->return_data(1,0,'验证通过');
            }
        }
    }


}
