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
use think\facade\Session;
class Basess extends Controller
{

    public function return_data($status=1,$error_no=0,$info='',$data=''){
        $status = empty($status)?false:true;
        if($status){
            $key = 'sinfo';
        }else{
            $key = 'emsg';
        }
        echo json_encode(['status'=>$status,'erno'=>$error_no,$key =>$info,'data'=>$data]);
        exit();
    }

}
