<?php
/**
 * 琴行机构
 * User: antony
 * Date: 2019/7/12
 * Time: 10:24
 */
namespace app\index\controller;
use think\Controller;
use think\Exception;
use think\Db;
use think\facade\Session;
use app\index\model\Organization as Organ;
use app\index\model\Users;

/*               真米如初
                 _oo0oo_
                o8888888o
                88" . "88
                (| -_- |)
                0\  =  /0
              ___/'---'\___
            .' \\|     |// '.
           / \\|||  :  |||// \
          / _||||| -:- |||||- \
         |    | \\\ - /// |    |
         | .-\  ''\---/''  /-. |
         \ . -\___ '-' ___/- . /
       ___'. .'   /--.--\  '. .'___
     /."" '< '.___\_<|>_/___.' >' "".\
    | | :  `- \'.;'\ _ /';.'/ -`  : | |
    \  \ '_.   \_ __\ /__ _/   .-` /  /
=====`-.____`.___ \_____/ ___.-`___.-'=====
                  '=----='
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
          佛祖保佑        永无Bug
*/

class Organization extends Basess
{
    /**
     * 新增
     */
    public function add(){
        $data = [
            'or_name'=>input('post.name'),
            'logo'=>input('post.logo'),
            'contact_man'=>input('post.contacts'),
            'telephone'=>input('post.phone'),
            'wechat'=>input('post.wechat'),
            'describe'=>input('post.intro'),
            'address'=>input('post.map'),
            'remarks'=>input('post.remarks'),
            'status' =>2,
             'uid'=>input('post.uid')
        ];

        $uid = input('post.uid');
        $validate = new \app\index\validate\Organization();
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|', $validate->getError());
//            var_dump($error);
            $this->return_data(0,$error[1], $error[0]);
        }
        Db::startTrans();
        try{
            $res = Organ::create($data);
            Db::commit();
            $where['organization'] = $res['id'];
            $where['update_time'] = time();
            //$where['manager'] = ret_session_name('uid');
            Users::where('uid',$uid)->update($where);
            $userinfo = Users::loginsession($uid);
            $rolelist =  $this->get_role_a($uid);
            $data['rolelist'] = $rolelist;
            $data['userinfo'] = $userinfo;
            $data['orgid'] = $res['id'];
            $this->return_data(1,0,$data);
        }catch (\Exception $e){
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }
    public  function  orglist()
    {
      $list = Organ::where('status',1)->select();
      $this->return_data(1,0,$list);
    }


    public function  get_role_a($uid)
    {
        $a =   json_decode($this->get_aid_role111($uid));
        $res = Db::table("erp2_user_accesses") ->where('access_id', 'in', $a)->where('pid',0)->select();
        foreach ($res as $k=>&$v)
        {
            $v['pidinfo'] = Db::table("erp2_user_accesses") ->where('access_id', 'in', $a)->where('pid',$v['access_id'])->where('type',1)->select();
            if(!empty($v['pidinfo'])){
                foreach ($v['pidinfo'] as $k1 => &$v1) {
                    $v1['pidinfos'] = Db::table("erp2_user_accesses") ->where('access_id', 'in', $a)->where('pid',$v1['access_id'])->where('type',2)->select();
                }
            }
        }
        return $res;
    }

    /*********************以下代码复制邱键的, ***************************/
    //获取当前用户的最终权限
    public  function  get_aid_role111($uid)
    {
        $userinfo = finds('erp2_users',['uid'=>$uid]);
        $rid = explode(',',is_string($userinfo['rid']));
        $array = [];
        foreach ($rid as $k=>$v){
            $array[] = finds('erp2_user_roles',['role_id'=>$v]);
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
    /*********************以上代码复制邱键的***************************/
    //根据获得当前机构列表
    public function get_org_list(){
       return $this->return_data(1,0,"查询成功",$this->get_org_list_m(input('post.or_id')));
    }
    //提取机构列表公共方法
    public static function get_org_list_m($or_id)
    {
        $org=finds('erp2_organizations',['is_del'=>0,'or_id'=>$or_id]);     //先查到这个机构
        $p_id=$org['uid'];//获得校长id

        if($p_id==0){//uid默认为0的话只查询当下一个机构
            $list=Organ::where('or_id',$or_id)->field('or_id, or_name,f')->select()->toArray();
            return $list;
        }
        $list = Organ::where('uid',$p_id)->field('or_id, or_name,f is null then 1')->select()->toArray();
        return $list;

    }


}