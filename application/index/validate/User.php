<?php
/**
 * 用户，校长表
 * User: antony
 * Date: 2019/7/11
 * Time: 10:23
 */
namespace app\index\validate;
use app\index\model\Users;
use think\facade\Cookie;
use think\Validate;
use think\Db;
use app\index\model\Organization;
class User extends Validate
{
    protected $rule = [
        'cellphone'=>'require|max:11|mobile|unique:Users',
        'password'=>'require|length:5,15',
        'repassword'=>'require|confirm:password',
        'remember'=>'integer|rem_password',
        'senfen'=>'require|number'
    ];
    protected $message = [
        'password.require'=>'密码不得为空|10000',
        'password.length'=>'密码长度不得小于5超过15|10001',
        'cellphone.require'=>'手机号不得为空|10000',
        'cellphone.mobile'=>'手机号格式不正确|10001',
        'cellphone.max'=>'手机号位数不正确|10001',
        'cellphone.unique'=>'手机号已经被注册|20000',
        'repassword.require'=>'确认密码不能为空|10000',
        'repassword.confirm'=>'两次密码不一致|10002',
        'remember.integer'=>'记住密码必须是整型（1记住0，0不记住）|10002',
        'senfen.require'=>'身份不能为空|10002',
        'senfen.number'=>'身份必须为数字|10001',
    ];
    public function sceneAdd()
    {
         return $this->only(['cellphone','password','repassword'])
             ->remove('password','length');
    }

    public function sceneEdit()
    {
        return $this->only(['cellphone','password','repassword'])
            ->remove('cellphone','unique')
            ->remove('password','length');
    }

    public function sceneLogin()
    {
         return $this->only(['cellphone','password','remember'])
             ->remove('cellphone','max|mobile|unique')
             ->remove('password','length');
    }


//    protected function check_user($password,$rule,$data)
//    {
//        $user_info = Users::where(['cellphone'=>$data['cellphone'],'password'=>md5_return($password)])->find();
//        if($user_info){
//            $orginfo =  Organization::where('or_id',$user_info['organization'])->find();
//            session(md5(MA.'user'),[
//                'id'=>$user_info['uid'],
//                'user_aco'=>$user_info['cellphone'],
//                'username'=>$user_info['nickname'],
//                'sex'=>$user_info['sex'],
//                'orgid'=>$user_info['organization'],
//                'config'=> [
//                    'or_id'      => $orginfo['or_id'],
//                    'name'       => $orginfo['or_name'],
//                    'logo'       => $orginfo['logo'],
//                    'contacts'   => $orginfo['contact_man'],
//                    'phone'      => $orginfo['telephone'],
//                    'wechat'     => $orginfo['wechat'],
//                    'intro'      => $orginfo['describe'],
//                    'map'        => $orginfo['address'],
//                    'remarks'    => $orginfo['remarks'],
//                ]
//            ]);
//            return true;
//        }else{
//        //return '用户名密码错误|20007';
//        $user_lits_info = Db::query("select * from user_list where status=1");
//        }
//    }
    /**
     * @param $is_rem
     * 记住密码
     */
    protected function rem_password($is_rem,$rule,$data)
    {
        if($is_rem == 1){
            cookie(base64_encode(MA.'userinfo'),[
                'account'=>base64_encode(MA.trim($data['cellphone'])),
                'pwd'=>base64_encode(MA.trim($data['password'])),
            ]);
            return true;
        }else{
            cookie(base64_encode(MA.'userinfo'),null);
            return true;
        }
    }


}



