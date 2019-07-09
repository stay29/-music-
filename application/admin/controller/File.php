<?php
namespace app\admin\controller;
use think\Controller;

class File extends Controller
{
    public function __construct(){
         parent::__construct();
    }

    /*
     * 公共上传方法
     * $user_define_dir：用户自定义的目录
     *
     */
    public function upload_file(){
        $type = input('type/d',1);
        $user_define_dir = input('dir','');
        if(!empty($user_define_dir) && !is_dir($user_define_dir)){
            mkdir($user_define_dir,0777);
        }
        
        $code = 0;
        $msg = '';
        $data['src'] = '';
        switch ($type){//图片
            case 1:
                $file = request()->file('file');
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $file->validate(['size'=>1024*1024*20,'ext'=>'jpg,png,gif,jpeg'])->rule('upload_file_rule')->move(UPLOAD_DIR.$user_define_dir);
                if($info){
                    if(!empty($user_define_dir)){
                        $data['src'] = DIRECTORY_SEPARATOR.UPLOAD_DIR.$user_define_dir.DIRECTORY_SEPARATOR.$info->getSaveName();
                    }else{
                        $data['src'] = DIRECTORY_SEPARATOR.UPLOAD_DIR.$info->getSaveName();
                    }

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
