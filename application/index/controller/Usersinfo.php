<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/18
 * Time: 16:02
 */
namespace app\index\controller;
use think\Controller;
use app\index\model\Users;
class Usersinfo extends BaseController
{
    public  function  addusers(){
        $data = [
            'nickname' =>input('post.nickname'),
            'cellphone' =>input('post.cellphone'),
            'password' =>input('post.password'),
            'rpassword' =>input('post.rpassword'),
            'organization' =>input('post.organization'),
            'organization' =>input('post.organization'),
            'senfen'=>input('post.senfen'),
            'sex'=>input('post.sex'),
        ];

    }




}