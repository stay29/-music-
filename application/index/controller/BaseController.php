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
use Firebase\JWT\JWT;//引入验证类
class BaseController extends Controller
{
    public function initialize()
    {   
        parent::initialize();
//        $tokenall =  $this->checkToken();
//        $token = db('Token_user')->where('uid',$tokenall['uid'])->find();
//        if ($token['token'] != $tokenall['token']) {
//            return $this->return_data(0, 10005, '请重新登录');
//        }
    }
    public function auth_get_token()
    {
        $mup['a_home'] = Request::instance()->module();
        $mup['a_coller'] = Request::instance()->controller();
        $mup['a_action'] = Request::instance()->action();
        $mup['is_del'] = 0;
        $mup['status'] = 1;
        $res = finds('erp2_user_accesses',$mup);
        if($res){
            $header = Request::instance()->header();
            if ($header['x-token'] == 'null'){
                $this->return_data('0', '10006', 'Token不存在，拒绝访问');
            }else{
                $checkJwtToken = $this->verifyJwt($header['x-token']);
                $uid = $header['x-uid'];
                $auth = $this->get_aid_role111($uid);
                $a = json_decode($auth,true);
                if(!in_array($res['access_id'],$a))
                {
                $this->return_data(0,0,'你没有改权限,请联系管理员');
                }
            }
        }else{
            $mup['create_time'] = time();
            $mup['update_time'] = time();
            $mup['sort'] = 1;
            $mup['status'] = 2;
            $mup['manager'] = '0';
            add('erp2_user_accesses',$mup);
        }


    }

    public  function  get_aid_role111($uid)
    {
        $userinfo = finds('erp2_users',['uid'=>$uid]);
        //print_r($userinfo);exit();
        if($userinfo){
            $rid = explode(',',is_string($userinfo['rid']));
            $array = [];
            foreach ($rid as $k=>$v){
             $iii =  finds('erp2_user_roles',['role_id'=>$v]);
             if($iii){
                 $array[] = $iii;
             }
        }
        }
        $arr = [];
        foreach ($array as $k1=>$v1){
            $arr []= explode(',',$v1['aid']);
        }
        $a = $this->array_heb($arr);
        $b =   $this->a_array_unique($a);
        return json_encode($b);
    }

    public  function array_heb($arrs)
    {
        static $arrays  = array();
        foreach ($arrs as $key=>$value)
        {
            if(is_array($value)){
                $this->array_heb($value);
            }else{
                $arrays[]= $value;
            }
        }
        return $arrays;
    }


    public function a_array_unique($array)//写的比较好
    {
        $out = array();
        foreach ($array as $key=>$value) {
            if (!in_array($value, $out))
            {
                $out[$key] = $value;
            }
        }
        return $out;
    }
    /**
     *响应
     *$info,在status=1返回成功提示，0的时候返回错误提示，$data返回需要的数据
     */
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

    public function sendMessage($phone,$msg,$sendtime='',$port='', $needstatus=''){
        $username = "zihao2"; //在这里配置你们的发送帐号
        $passwd = "JBZ992888";    //在这里配置你们的发送密码
        $ch = curl_init();
//        $post_data = "username=".$username."&passwd=".$passwd."&phone=".$phone."&msg=".urlencode($msg)."&needstatus=true&port=".$port."&sendtime=".$sendtime;
//
// php5.4或php6 curl版本的curl数据格式为数组你们接入时要注意
        $post_data = array(
        "username"=> $username,
        "passwd"=> $passwd,
        "phone" => "13700859247",
        "msg" => "您好,你的验证码:8888【企业宝】",
        "needstatus"=>"true",
        "port"=>'',
        "sendtime"=>'',
        );
        curl_setopt ($ch, CURLOPT_URL,"http://www.qybor.com:8500/shortMessage");
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return json_decode($file_contents);
    }
    /*
    * 富文本图片上传重复公共处理器，供含有富文本的编辑操作过滤器
     * $str:富文本内容，
    * $arr:编辑页内容包含图片，需要正则匹配
     */
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

    public function checkToken()
    {
        $header = Request::instance()->header();
        //print_r($header);exit();
        if(array_key_exists('x-token',$header)){
        if ($header['x-token'] == 'null'){
            $this->return_data('0', '10006', 'Token不存在，拒绝访问');
        }else{
            $checkJwtToken = $this->verifyJwt($header['x-token']);
            if ($checkJwtToken['status'] == 1) {
                $data['token'] = $header['x-token'];
                $data['uid'] = $header['x-uid'];
                return $data;
            }
        }
        }else{
            $this->return_data('0', '10006', 'Token不存在，拒绝访问');
        }
    }
    //校验jwt权限API
    protected function verifyJwt($jwt)
    {
        $key = md5('nobita');
        // JWT::$leeway = 3;
        try {
            $jwtAuth = json_encode(JWT::decode($jwt, $key, array('HS256')));
            $authInfo = json_decode($jwtAuth, true);
            $msg = [];
            if (!empty($authInfo['data']['id'])) {
                $msg = [
                    'status' => 1,
                    'msg' => 'Token验证通过'
                ];
            } else {
                $this->return_data('0', '10004', '请重新登录');
            }
            return $msg;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $this->return_data(0, '10004', 'Token无效');
        } catch (\Firebase\JWT\ExpiredException $e) {
            $this->return_data(0, '10005', 'token过期');
        } catch (Exception $e) {
            $this->return_data(0, '50000', '未知错误，请检查');
        }
    }
}