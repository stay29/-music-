<?php
namespace app\admin\controller;
use think\Controller;

class File extends Controller
{
    public function __construct(){
         parent::__construct();
    }

    //上传
    public function upload_file($type=1){
        
        $code = 0;
        $msg = '';
        $data['src'] = '';
        switch ($type){//图片
            case 1:
                $file = request()->file('file');
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $file->validate(['ext'=>'jpg,png,gif'])->rule('upload_file_rule')->move(UPLOAD_DIR);
                if($info){
                    $data['src'] = DIRECTORY_SEPARATOR.UPLOAD_DIR.$info->getSaveName();
                }else{
                    // 上传失败获取错误信息
                    $msg =  $file->getError();
                    $code = 1;
                }
                break;
        }
        echo json_encode(['code'=>$code,'msg'=>$msg,'data'=>$data]);die;
    }


   
}
