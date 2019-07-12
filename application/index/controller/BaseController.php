<?php
/**
 * 基础控制器
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */

namespace app\index\controller;
use think\Controller;
use think\facade\Request;
class BaseController extends Controller
{
    public function initialize()
    {
        parent::initialize();
//session(md5(MA.'user'),null);die;
        $controller =  Request::controller();;
        $action =  Request::action();
        if($controller == 'Login' || $controller == 'Currm'){
            if(session('?'.md5(MA.'user'))){
              //  $this->return_data(0,20006,'无须再次登录！');
            }
        }else{
            if(!session('?'.md5(MA.'user'))){
               // $this->return_data(0,20008,'请登录后再来！谢谢合作！');
            }
        }
    }

    /**
     *响应
     *$info,在status=1返回成功提示，0的时候返回错误提示，$data返回需要的数据
     */
    public function return_data($status=1,$error_no=0,$info,$data=''){
        $status = empty($status)?false:true;
        if($status){
            $key = 'sinfo';
        }else{
            $key = 'emsg';
        }
        echo json_encode(['status'=>$status,'erno'=>$error_no,$key =>$info,'data'=>$data]);die;
    }


}