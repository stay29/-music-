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

//获取https的内容
function curl_https($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
// 上传文件规则，用于tp5.1的自动上传命名
function upload_file_rule(){
    $file_name = time();
	if(session('?admin')){
		$file_name = 'temp'.DIRECTORY_SEPARATOR.date('Y-m-d').DIRECTORY_SEPARATOR.uniqid();
	}
	return $file_name;
}

/**
 * 清空/删除 文件夹
 * @param string $dirname 文件夹路径
 * @param bool $self 是否删除当前文件夹
 * @return bool
 */
function do_rmdir($dirname, $self = true) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if ($dir) {
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            do_rmdir($dirname . '/' . $entry);
        }
    }
    $dir->close();
    $self && rmdir($dirname);
}

/**
 * @param $arr
 * @param int $type
 * 打印数组
 */
function dd($arr,$type=1){
    echo '<pre>';
    if($type ==1){
        print_r($arr);
    }elseif($type == 2){
        var_dump($arr);
    }
    echo '</prev>';
    exit();
}


//返回加密后的密码
   function  md5_return($password){
    $password = md5(md5(md5(MA.$password)));
    return $password;
}
//验证码发送


