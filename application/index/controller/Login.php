<?php
/**
 * 登录
 * User: antony
 * Date: 2019/7/11
 * Time: 15:43
 */
namespace app\index\controller;
use think\Controller;
use think\Exception;
use app\index\model\Users;
use app\index\validate\User;
use think\Db;
use think\facade\Session;
use think\facade\Cookie;
use app\index\model\Organization as Organ;
class Login extends Basess{

    public function for_login()
    {
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
                 //查询判断新用户还是爱琴家用
                 $mup['cellphone'] = $data['cellphone'];
                 $mup['password'] =md5_return($data['password']);
                 $mup['incumbency'] =1;
                 $user_login_info = Users::where($mup)->find();
                 if($user_login_info){
                     //存入session
                     $arr = Users::loginsession($user_login_info['uid']);
                     //生成token和其他操作
                      $arr1 = [
                          'id' => $user_login_info['uid'],
                          'cellphone' => $user_login_info['cellphone'],
                          'orgid' => $user_login_info['organization'],
                          'nickname' => $user_login_info['nickname'],
                      ];
                     $token =  Users::login_token($arr1,$user_login_info['uid']);
                     $arr['token'] = $token;
                     $this->return_data(1,0,'登录成功',$arr);
                 }else{
                         $mup1['cellphone'] = $data['cellphone'];
                          $user_login_info1 =    Users::where($mup1)->find();
                          if($user_login_info1['incumbency']==2){
                              $this->return_data(0,60000,'你已经离职无法登陆平台');
                          }
                          if($user_login_info1){
                              $this->return_data(0,60000,'密码错误请重新登录');
                          }else{
                              $this->return_data(0,20007,'用户还没有注册 请注册后登陆');
                          }
                      }
                 }
             }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }


    public function for_login_old()
    {
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
                //查询判断新用户还是爱琴家用
                $mup['cellphone'] = $data['cellphone'];
                $mup['password'] =md5_return($data['password']);
                $user_login_info =    Users::where($mup)->find();
                //判断是不是重复登陆
                $arr_sess = Session::get($user_login_info['uid']);
                if($arr_sess!=null){
                    $this->return_data(0,10000,'请不要重复登陆');
                }
                if($user_login_info){
                    $arr = Users::loginsession($user_login_info['uid']);
                    $this->return_data(1,0,'登录成功',$arr);
                }else{
                    //如果不是erp用户则查询爱琴家用户
                    $account= $data['cellphone'];
                    $accountpassword =  md5_return_aqj($data['password']);
                    $aqj_user_info = Db::query("select * from user_list where account=? AND password=?  AND role=?", [$account,$accountpassword,3]);
                    if($aqj_user_info){
                        //生成erp用户和机构  1添加用户 2添加机构
                        //生成机构
                        $data2['or_name'] = $aqj_user_info[0]['nickname'];
                        $data2['contact_man'] = $aqj_user_info[0]['nickname'];
                        $data2['telephone'] = $aqj_user_info[0]['account'];
                        $data2['mobilephone'] = $aqj_user_info[0]['account'];
                        $data2['status'] = 2;
                        $orginfo = Organ::create($data2);
                        $data1['cellphone'] = $aqj_user_info[0]['account'];
                        $data1['password'] = md5_return($data['password']);
                        $data1['nickname'] = $aqj_user_info[0]['nickname'];
                        $data1['account'] = $aqj_user_info[0]['account'];
                        $data1['organization'] = $orginfo['id'];
                        $ol_user_info = Users::adduser_info($data1);
                        $arr = Users::loginsession($ol_user_info['uid']);
                        $this->return_data(1,0,'登陆成功',$arr);
                    }else{
                        $this->return_data(0,20007,'用户名密码错误');
                    }
                }
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
            'senfen' => input('post.senfen'),
        ];
        $vieryie = input('post.vieryie');
        if($vieryie !=Session::get('vieryie')){
            $this->return_data(0,0,'验证码不一致');
        }
        // 启动事务
        Db::startTrans();
        try{
            $validate = new \app\index\validate\User();
            if(!$validate->scene('add')->check($data)){
                //为了可以得到错误码
                $error = explode('|',$validate->getError());
                $this->return_data(0,$error[1],$error[0]);
                exit();
            }else{
            $mup['account']   = $data['cellphone'];
            $mup['cellphone'] = $data['cellphone'];
            $mup['password']  = md5_return($data['password']);
            $res = Users::addusers($mup);
            Db::commit();
            $userinfo = Users::loginsession($res);
            $this->return_data(1,0,$userinfo);
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }
    //退出登录
    public  function  logout()
    {
        session(null);
        $this->return_data(1,0,'退出登录');
    }

     //验证码获取
    public  function  get_vieryie()
    {
        $pione = input('user_aco');
        if($pione){
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
            Session::init([
                'expire'=> 10,
            ]);
            Session::set('vieryie',$output);
            $this->return_data(1,0,$output);
        }else{
            $this->return_data(0,10000,'请输入手机号');
        }
    }

    //忘记密码
    public  function  editpassword(){
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
            if(!$validate->scene('edit')->check($data)){
                //为了可以得到错误码
                $error = explode('|',$validate->getError());
                $this->return_data(0,$error[1],$error[0]);
            }else{
                $mup['account']   = $data['cellphone'];
                $oldinfo =Users::get_one_info($mup);
                if($oldinfo)
                {
                    $mup['uid'] = $oldinfo['uid'];
                }else{
                    $this->return_data(0,10000,'用户不存在请先注册');
                }
                $mup['cellphone'] = $data['cellphone'];
                $mup['password']  = md5_return($data['password']);
                $res = Users::edit_one_info($mup['uid'],$mup);
                session(null);
                $this->return_data(1,0,'修改成功');
            }
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }
}
