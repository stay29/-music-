<?php
namespace app\index\model;
use think\Model;
use think\Db;
use app\index\model\Organization as Organ;
use think\facade\Session;
use app\index\controller\Jwttoken;
class Users extends Model
{
	protected $table = 'erp2_users';
    protected $pk = 'uid';
    protected $field = true;
    protected $autoWriteTimestamp = true;
    protected $insert = ['status'=>1];
    public function returnUser()
    {
        return $this->belongsTo('Curriculums','uid');
    }
    
    public  static  function  login_token($arr,$uid)
    {
        $Jwttoken =  new Jwttoken;
        $token = $Jwttoken->createJwt($arr);
        //查询token表
        $tokeninfo = db('Token_user')->where('uid',$uid)->find();
        $mup['token'] = $token;
        if($tokeninfo){
            $mup['create_time'] = time();
            $token_db =db('Token_user')->where('uid',$uid)->update($mup);
            session('token',$token);
        }else{
            $mup['uid'] = $uid;
            $mup['create_time'] = time();
            $token_db =db('Token_user')->insert($mup);
            session('token',$token);
        }
        return $token;
    }

    public  static  function  loginsession($uid){
        $user_info = Users::where('uid',$uid)->find();
        $orginfo = Organ::where('or_id',$user_info['organization'])->find();
        $arr =  [
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
                'pay_state'    => $orginfo['pay_state'],
            ]
        ];
        session(md5(MA.'user'), $arr);
        Session::set($arr['id'],$arr);
        return $arr;
    }



    public  static function  addusers($data)
    {
        $res = Users::create($data, true);
        $suid = session(md5(MA.'user'))['id'];
        Users::where(['uid'=>$res->uid])
        ->update(['nickname'=>'校长'.$res->uid,'update_time'=>time(),'login_time'=>time()]);
        return $res->uid;
    }
    public  static  function  adduser_info($data){
        $res = Users::create($data, true);
        return $res;
    }
    public static function  get_one_info($data)
    {
        $res = Users::where($data)->find();
        return $res;
    }
    public static function  edit_one_info($uid,$data)
    {
       $res =  Users::where($uid)->update($data);
       return $res;
    }

}