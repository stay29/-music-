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

//返回加密后的密码 erp加密
function  md5_return($password){
    $password = md5(md5(md5(MA.$password)));
    return $password;
}
//返回加密后的密码 爱情家加密
function  md5_return_aqj($password){
    $password = md5(MB.md5($password));
    return $password;
}

    //二维数组下表替换
 function  array_serch($key,$array) {
    $new_array = array();
    foreach($array as $k=>$v) {
        $new_array[$k] = array_combine($key,$v);
    }

    return $new_array;
}

//返回uid 或机构idsession
function ret_session_name($name=''){
    if($name==null){
        $uid =  session(md5(MA.'user'));
    }elseif($name=='uid'){
        $uid =  session(md5(MA.'user'))['id'];
    }elseif ($name=='orgid'){
        $uid =  session(md5(MA.'user'))['orgid'];
    }
    return $uid;
}
//刷新session
function shua_session(){
    $uid = ret_session_name('uid');
    $user_info = db('users')->where('uid',$uid)->find();
    $orginfo =  Organization::where('or_id',$user_info['organization'])->find();
    session(md5(MA.'user'),[
        'id'=>$user_info['uid'],
        'user_aco'=>$user_info['cellphone'],
        'username'=>$user_info['nickname'],
        'sex'=>$user_info['sex'],
        'orgid'=>$user_info['organization'],
        'config'=> [
            'or_id'      => $orginfo['or_id'],
            'name'       => $orginfo['or_name'],
            'logo'       => $orginfo['logo'],
            'contacts'   => $orginfo['contact_man'],
            'phone'      => $orginfo['telephone'],
            'wechat'     => $orginfo['wechat'],
            'intro'      => $orginfo['describe'],
            'map'        => $orginfo['address'],
            'remarks'    => $orginfo['remarks'],
        ]
    ]);
}







//删除数组元素一维数组一个或者多个
    function del_array_info($array,$info){
        $res= array_diff_key($array, $info);
        return $res;
    }


//通用返回应该字段
function getname($dataName,$where,$getname){
    $aaa = Db::table($dataName)->where($where)->find();
    return $aaa[$getname];
}

function getnameselect($dataName,$where,$getname){
    $aaa = Db::table($dataName)->where($where)->select();
    $fff = [];
    foreach ($aaa as $k=>$v){
        $fff[] = $v[$getname];
    }
    return $fff;
}



//通用单条查询
function finds($dataName,$where,$field=''){
    return Db::table($dataName)->field($field)->where($where)->find();
}
//通用筛选查询
function select_find($dataName,$where,$field='')
{
     return Db::table($dataName)->field($field)->where($where)->select();
}

//通用查询
function selects($dataName,$where){
    return Db::table($dataName)->where($where)->select();
}
//通用增加
function add($dataName,$data,$key=''){
    if($key==1){
        return Db::table($dataName)->data($data)->insert();
    }else{
        return Db::table($dataName)->insertGetId($data);
    }

}
//通用删除
function del($dataName,$where){
    return Db::table($dataName)->where($where)->delete();
}
//通用修改
function edit($dataName,$where,$data){
    return Db::table($dataName)->where($where)->update($data);
    //echo M($dataName)->_sql();die;//输出sql语句
}
//通用输出sql
function getsql($dataName){
    return Db::table($dataName)->getLastSql();
}








