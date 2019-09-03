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
function upload_file_rule(){
    $file_name = time();
	if(session('?admin')){
		$file_name = 'temp'.DIRECTORY_SEPARATOR.date('Y-m-d').DIRECTORY_SEPARATOR.uniqid();
	}
	return $file_name;
}

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

function  md5_return($password){
    $password = md5(md5(md5(MA.$password)));
    return $password;
}
function  md5_return_aqj($password){
    $password = md5(MB.md5($password));
    return $password;
}

 function  array_serch($key,$array) {
    $new_array = array();
    foreach($array as $k=>$v) {
        $new_array[$k] = array_combine($key,$v);
    }
    return $new_array;
}

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

 function  getsession()
{
    $a = getconfig();
    if($a!='192.168.1.88'){
        $f1 =  session('a');
        if($f1==null){
            $f =  session('a',1);
            return 1;
        }else{
            if($f1 == '1' or $f1>'1'){
                $f =  session('a',$f1+1);
                $f2 = session('a');
                return $f2;
            }
        }
    }
}



    function del_array_info($array,$info){
        $res= array_diff_key($array, $info);
        return $res;
    }


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

function getconfig()
{
    $a = request()->ip();
    return $a;
}

function finds($dataName,$where,$field=''){
    return Db::table($dataName)->field($field)->where($where)->find();
}

function select_find($dataName,$where,$field='')
{
     return Db::table($dataName)->field($field)->where($where)->select();
}

function selects($dataName,$where){
    return Db::table($dataName)->where($where)->select();
}

function add($dataName,$data,$key=''){
    if($key==1){
        return Db::table($dataName)->data($data)->insert();
    }else{
        return Db::table($dataName)->insertGetId($data);
    }

}

function del($dataName,$where){
    return Db::table($dataName)->where($where)->delete();
}
function edit($dataName,$where,$data,$l=null){
    if($l==null){
        return  Db::table($dataName)->where($where)->update($data);
    }
}
function getsql($dataName){
    return Db::table($dataName)->getLastSql();
}


/*
 * 判断不确定参数是否为空。
 * 若所有参数均不为空返回false, 其中一个为空返回true。
 */
function is_empty(...$args)
{
    foreach ($args as $val)
    {
        if (empty($val))
        {
            return true;
        }
    }
    return false;
}




/*
 * 生成单号
 */
function random_code()
{
    mt_srand((double) microtime() * 1000000);
    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}


/*
 * 数组转字符
 */

function arr2str()
{

}

/*
 * 无限极分类树
 */
function getTree($arr){
    $refer = array();
    $tree = array();
    foreach($arr as $k => $v){
        $refer[$v['id']] = & $arr[$k];  //创建主键的数组引用
    }

    foreach($arr as $k => $v){
        $pid = $v['cate_pid'];   //获取当前分类的父级id
        if($pid == 0){
            $tree[] = & $arr[$k];   //顶级栏目
        }else{
            if(isset($refer[$pid])){
                $refer[$pid]['sub'][] = & $arr[$k];  //如果存在父级栏目，则添加进父级栏目的子栏目数组中
            }
        }
    }

    return $tree;
}
