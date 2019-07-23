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

class BaseController extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->checkToken();
//         $user_sess_info =Session::get(md5(MA.'user'));
//         if($user_sess_info==null){
//                return $this->return_data(0,10000,'sss');
//         }
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
            $key = 'emsg';
        }
        echo json_encode(['status'=>$status,'erno'=>$error_no,$key =>$info,'data'=>$data]);
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
        if ($header['authorization'] == 'null'){
            $this->return_data('0', '10000', 'Token不存在，拒绝访问');
        }else{
            $checkJwtToken = $this->verifyJwt($header['authorization']);
            if ($checkJwtToken['status'] == 1) {
                return true;
            }
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
            if (!empty($authInfo['user_id'])) {
                $msg = [
                    'status' => 1,
                    'msg' => 'Token验证通过'
                ];
            } else {
                $this->return_data('0', '10003', 'Token验证不通过,用户不存在');
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

