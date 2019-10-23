<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Login extends Controller
{ 
public function index(Request $request){
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        // 参数
        $params['appid']= config('WX_APPID');        
        $params['secret']= config('WX_APPSECRET');       
        $params['js_code']= $request -> param('code');        
        $params['grant_type']= 'authorization_code';
        $user_phone= $request -> param('user_phone');        
        // 微信API返回的session_key 和 openid
        $arr = httpCurl($url, $params);        
        $arr = json_decode($arr,true);        
        // 判断是否成功
        if(isset($arr['errcode']) && !empty($arr['errcode'])){            
            return json(['code'=>'2','message'=>$arr['errmsg'],"result"=>null]);
        }        
        $openid = $arr['openid'];        
        $session_key = $arr['session_key'];        
        // 从数据库中查找是否有该openid
        $is_openid = Db::table('user_info')->where('openid',$openid)->find();        
        // 如果openid存在，更新openid_time,返回登录成功信息及手机号
        if($is_openid){            
        // openid存在，先判断openid_time,与现在的时间戳相比，如果相差大于4个小时，则则返回登录失败信息，使客户端跳转登录页，如果相差在四个小时之内，则更新openid_time，然后返回登录成功信息及手机号；
            // 根据openid查询到所在条数据
            $data = Db::table('user_info')->where('openid',$openid)->find();            
            // 计算openid_time与现在时间的差值
            $time = time() - $data['openid_time'];            
            $time = $time / 3600;            
            // 如果四个小时没更新过，则登陆态消失，返回失败，重新登录
            if($time > 4){                
            return json(['sendsure'=>'0','message'=>'登录失败',]);
            }else{
            // 根据手机号更新openid时间
                $update = Db::table('user_info')->where('openid', $openid)->update(['openid_time' => time()]);                
                // 判断是否更新成功
                if($update){                    
                    return json(['sendsure'=>'1','message'=>'登录成功','user_phone' => $data['user_phone']]);
                }else{                    
                    return json(['sendsure'=>'0','message'=>'登录失败']);
                }
            }        
            // openid不存在时
        }else{            
        // dump($user_phone);
            // 如果openid不存在, 判断手机号是否为空
            if(isset($user_phone) && !empty($user_phone)){                
            // 如果不为空，则说明是登录过的，就从数据库中找到手机号，然后绑定openid，+时间
                // 登录后,手机号不为空，则根据手机号更新openid和openid_time
                $update = Db::table('user_info')
                    ->where('user_phone', $user_phone)
                    ->update([                        
                    'openid'  => $openid,                        
                    'openid_time' => time(),
                    ]);                
                if($update){        
                    return json(['sendsure'=>'1','message'=>'登录成功',]);
                }
            }else{                
            // 如果也为空，则返回登录失败信息，使客户端跳转登录页
                return json(['sendsure'=>'0','message'=>'读取失败',]);
            }
        }
    }
    
    //家长登录
    public function login(Request $request){
        // 获取到前台传输的手机号
        $user_phone = $request -> param('user_phone');        
        // 判断数据库中该手机号是否存在
        $is_user_phone = Db::table('user_info')->where('user_phone',$user_phone)->find();        
        if(isset($is_user_phone) && !empty($is_user_phone)){            
        // 登录时，数据库中存在该手机号，则更新openid_time
            $update = Db::table('user_info')
                    ->where('user_phone', $user_phone)
                    ->update([                        
                    'openid_time' => time(),
                    ]);            
            if($update){                
                    return json(['sendsure'=>'1','message'=>'登录成功',]);
            }
        }else{            
            $data = [                
                "user_phone" => $user_phone,                
                "pass" => '12345'
            ];           
            // 如果数据库中不存在该手机号，则进行添加
            Db::table('user_info')->insert($data);
        }        return json(['sendsure'=>'1','message'=>'登录成功',]);
    }
    
    //教师登录
    public function teacher_login(Request $request){
        // 获取到前台传输的手机号
        $user_phone = $request -> param('user_phone');        
        // 判断数据库中该手机号是否存在
        $is_user_phone = Db::table('user_info')->where('user_phone',$user_phone)->find();        
        if(isset($is_user_phone) && !empty($is_user_phone)){            
        // 登录时，数据库中存在该手机号，则更新openid_time
            $update = Db::table('user_info')
                    ->where('user_phone', $user_phone)
                    ->update([                        
                    'openid_time' => time(),
                    ]);            
            if($update){                
                    return json(['sendsure'=>'1','message'=>'登录成功',]);
            }
        }else{            
            $data = [                
                "user_phone" => $user_phone,                
                "pass" => '12345'
            ];           
            // 如果数据库中不存在该手机号，则进行添加
            Db::table('user_info')->insert($data);
        }        return json(['sendsure'=>'1','message'=>'登录成功',]);
    }
}
