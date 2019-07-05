<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
  //返回json数据并停止执行
 function return_data($status,$error_code=0,$data=null){
    $res = array();
    $res['status']=empty($status)?false:true;
    $res['error_code']=intval($error_code);
    $res['error_msg']='';
    if(!empty($error_code)){
      if(is_string($data))
        $res['error_msg']=$data;
      else{
        switch ($res['error_code'])
        {
          case 1:
            $res['error_msg']='操作成功';
            break;
          case 2:
            $res['error_msg']='操作失败';
            break;
          default:
              $res['error_msg']='';
        }
      }
    }
    if(isset($data)){
      if(!is_string($data)||$res['status']) $res['data']=$data;
    }
    //$res['timestamp']=time()*1000;
    //echo json_encode($res);
    echo json_encode($res, /*JSON_NUMERIC_CHECK | 数值过长时会出错*/ JSON_UNESCAPED_UNICODE);//数值型不加引号
    exit;
  }