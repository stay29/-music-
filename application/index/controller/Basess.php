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
use think\Db;
use think\facade\Session;

class Basess extends Controller
{
    public function return_data($status=1,$error_no=0,$info='',$data=''){
        $status = empty($status)?false:true;
        if($status){
            $key = 'sinfo';
        }else{
            $key = 'error_msg';
        }
        echo json_encode(['status'=>$status,'error_code'=>$error_no,$key =>$info,'data'=>$data]);
        exit();
    }

    public static function return_data_sta($status=1,$error_no=0,$info='',$data=''){
        $status = empty($status)?false:true;
        if($status){
            $key = 'sinfo';
        }else{
            $key = 'error_msg';
        }
        echo json_encode(['status'=>$status,'error_code'=>$error_no,$key =>$info,'data'=>$data]);
        exit();
    }

    //图片上传
    public  function  get_ret_img_update($name,$path){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($name);
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->rule('uniqid')->move($path);
        if($info){
            return $info->getSaveName();
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }

    //上传图片
    public function get_img_update()
    {
        $res =  $this->get_ret_img_update('img','./upload/currm/');
        $imgpath = './upload/currm/'.$res;
        $this->return_data(1,0,$imgpath);
    }

    //删除图片
    public  function  get_img_del()
    {
        $oldig = input('oldimg');
        $res = file_exists($oldig);
        if($res){
            unlink($oldig);
            $this->return_data(1,0,'删除成功');
        }else{
            $this->return_data(0,50000,'删除失败');
        }
    }
}

