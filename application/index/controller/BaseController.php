<?php
/**
 * 基础控制器
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */

namespace app\index\controller;


use think\Controller;

class BaseController extends Controller
{

    public function initialize()
    {
        parent::initialize();
//        $md5_user = md5('user');
//        if(!session('?'.$md5_user)){
//            $this->return_data(0,450,'请先登录');
//        }else{
//            $session_userid = session($md5_user);
//            if(isset($session_userid) && !empty($session_userid)){
//                $this->session_userid = session($md5_user['userid']);
//            }
//        }
    }

    /**
     *响应
     *$info,在status=1返回成功提示，0的时候返回错误提示，$data返回需要的数据
     */
    public function return_data($status,$error_no=0,$info,$data=''){
        $status = empty($status)?false:true;
        if($status){
            $key = 'sinfo';
        }else{
            $key = 'emsg';
        }
        echo json_encode(['status'=>$status,'erno'=>$error_no,$key =>$info,'data'=>$data]);die;
    }
}