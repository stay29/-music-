<?php
/**
 * 琴行机构
 * User: antony
 * Date: 2019/7/12
 * Time: 10:24
 */
namespace app\index\controller;
use app\index\model\Banner;
use app\index\model\DynamicState;
use app\index\model\Record;
use think\Controller;
use think\Exception;
use think\Db;
use think\facade\Request;
use think\facade\Session;
use app\index\model\Organization as Organ;
use app\index\model\Users;
use app\index\model\Seniorities as SenModel;
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
             'uid'=>input('post.uid'),
            'lng'=>input('post.lng'),
            'lat'=>input('post.lat')
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
            $where['organization'] = $res['id'];
            $where['update_time'] = time();
            //$where['manager'] = ret_session_name('uid');
            Users::where('uid',$uid)->update($where);
            $userinfo = Users::loginsession($uid);
            $rolelist =  $this->get_role_a($uid);
            $data['rolelist'] = $rolelist;
            $data['userinfo'] = $userinfo;
            $data['orgid'] = $res['id'];
            //给机构添加默认的资历
            $senarray=SenModel::where(['is_official'=>1,'is_del'=>0])->select();
            foreach ($senarray as $key=>$value){
                $sendata = [
                    'seniority_name' => $value['seniority_name'],
                    'sort' => $key,
                    'is_del' => 0,
                    'org_id' => $res['id']
                ];
                SenModel::create($sendata);
                Db::commit();
            }
            //返回数据
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
       return $this->return_data(1,0,"查询成功",$this->get_org_list_m(input('post.orgid')));
    }
    //提取机构列表公共方法
    public static function get_org_list_m($or_id)
    {
        $org=finds('erp2_organizations',['is_del'=>0,'or_id'=>$or_id]);     //先查到这个机构
        $p_id=$org['uid'];//获得校长id

        if($p_id==0){//uid默认为0的话只查询当下一个机构
            $list=Organ::where('or_id',$or_id)->field('or_id, or_name')->select()->toArray();
            return $list;
        }
        $list = Organ::where('uid',$p_id)->field('or_id, or_name')->select()->toArray();
        return $list;

    }
    /**
     * 获取单个机构的信息
     */
    public function get_org_info(){
        $or_id= Request::instance()->header()['orgid'];  //从header里面拿orgid
        $org=Organ::where('or_id',$or_id)->field('or_name,logo,describe,address,contact_man,telephone,wechat,lng,lat')->find();
        return $this->return_data(1,0,"",$org);
    }
    /*
     * 修改机构信息
     */
    public function edit_org_info(){
        $or_id= Request::instance()->header()['orgid'];  //从header里面拿orgid
        $data=input();
        Db::startTrans();
        try{
            Organ::where('or_id',$or_id)->update($data);
            Db::commit();
            return $this->return_data(1,0,"修改成功！","");
        }catch (Exception $e){
            Db::rollback();
            $this->return_data(0,20002,$e->getMessage(),"");
        }

    }
    /**
 * 发布机构新动态
 */
    public  function add_dynamic_state(){
        $data=input();
        $data['or_id']=Request::instance()->header()['orgid'];
        $data['create_time']=time();
        Db::startTrans();
        try{
            DynamicState::create($data);
            Db::commit();
            $this->return_data(1,0,"发布成功！");
        }catch(Exception $e){
            Db::rollback();
            $this->return_data(0,20001,$e->getMessage());
        }


    }
    /**
     * 获得机构动态列表
     */
    public  function get_ds_list(){
        $limit = input('limit/d', 20);
        try{
            $or_id=Request::instance()->header()['orgid'];
            $res_data=  DynamicState::where(['or_id'=>$or_id,'is_del'=>0])->order('create_time','desc')->paginate($limit);
            $this->return_data(1,0,"",$res_data);
        }catch(Exception $e){
            $this->return_data(0,20001,$e->getMessage());
        }


    }
    /**
     * 编辑机构新动态
     */
    public  function edit_dynamic_state(){
        $data=input();
//        $or_id=Request::instance()->header()['orgid'];
        $ds_id=input('post.ds_id');
        Db::startTrans();
        try{
            DynamicState::where('ds_id',$ds_id)->update($data);
            Db::commit();
            $this->return_data(1,0,"更新成功！","");
        }catch(Exception $e){
            Db::rollback();
            $this->returnError(20002,$e->getMessage());
        }
    }
    /**
     * 删除机构动态
     */
    public  function del_dynamic_state(){
        $data['is_del']=1;
//        $or_id=Request::instance()->header()['orgid'];
        $ds_id=input('post.ds_id');
        Db::startTrans();
        try{
            DynamicState::where('ds_id',$ds_id)->update($data);
            Db::commit();
            $this->return_data(1,0,"删除成功！","");
        }catch(Exception $e){
            Db::rollback();
            $this->return_data(0,20002,$e->getMessage());
        }
    }
    /**
     * 发布机构banner
     */
    public function add_banner(){
        $data=input();
        $data['or_id']=Request::instance()->header()['orgid'];
        $data['update_time']=time();
        Db::startTrans();
        try{
            Banner::create($data);
            Db::commit();
            $this->return_data(1,0,"发布成功！","");
        }catch(Exception $e){
            Db::rollback();
            $this->return_data(0,20001,$e->getMessage());
        }

    }
    /**
     * 获得机构banner列表
     */
    public  function get_banner_list(){
        $limit = input('limit/d', 20);
        try{
            $or_id=Request::instance()->header()['orgid'];
            $res_data=  Banner::where(['or_id'=>$or_id,'is_del'=>0])->order('update_time','desc')->paginate($limit);
            $this->return_data(1,0,"",$res_data);
        }catch(Exception $e){
            $this->return_data(0,20001,$e->getMessage(),"");
        }


    }
    /**
     * 编辑机构banner
     */
    public function edit_banner(){
        $data=input();
//        $or_id=Request::instance()->header()['orgid'];
        $b_id=input('post.b_id');
        $data['update_time']=time();
        Db::startTrans();
        try{
            Banner::where('b_id',$b_id)->update($data);
            Db::commit();
            $this->return_data(1,0,"更新成功！","");
        }catch(Exception $e){
            Db::rollback();
            $this->return_data(0,20002,$e->getMessage());
        }

    }
    /**
     * 删除机构banner
     */
    public function del_banner(){
        $data['is_del']=1;
//        $or_id=Request::instance()->header()['orgid'];
        $b_id=input('post.b_id');
        $data['update_time']=time();
        Db::startTrans();
        try{
            Banner::where('b_id',$b_id)->update($data);
            Db::commit();
            $this->return_data(1,0,"删除成功！","");
        }catch(Exception $e){
            Db::rollback();
            $this->return_data(0,20002,$e->getMessage());
        }

    }

    /**
     * 置顶banner图
     */
    public function stick_banner(){
//        $or_id=Request::instance()->header()['orgid'];
        $b_id=input('post.b_id');
        $data['update_time']=time();
        Db::startTrans();
        try{
            Banner::where('b_id',$b_id)->update($data);
            Db::commit();
            $this->return_data(1,0,"置顶成功！");
        }catch(Exception $e){
            Db::rollback();
            $this->return_data(0,20002,$e->getMessage());
        }
    }
    /**
     * 下架banner图
     */
    public function sold_out_b(){
        $data['sold_out']=input('post.sold_out');
        $b_id=input('post.b_id');
        $data['update_time']=time();
        Db::startTrans();
        try{
            Banner::where('b_id',$b_id)->update($data);
            Db::commit();
            $this->return_data(1,0,"操作成功！");
        }catch(Exception $e){
            Db::rollback();
            $this->return_data(0,20002,$e->getMessage());
        }
    }
    /**
     * 获得机构操作记录
     * @Param  userid  用户id
     * @Param  o_type  操作类型
     * @Param  s_time  开始时间
     * @Param  e_time 结束时间
     * @param  key  关键词
     * @param  limit 每页多少个数量
     */
     public function  get_record(){
         $or_id=Request::instance()->header()['orgid'];
         $userid=Request::instance()->header()['x_userid'];
         $o_type=input('post.o_type');
         $key=input('post.key');
         $limit=input('post.limit',20);
         $where[]=['or_id','=',$or_id];
         if($userid){
             $where[]=['userid','=',$userid];
         }
         if($o_type){
             $where[]=['o_type','=',$o_type];
         }
         if($key){
             $where[]=['key','like','%'.$key.'%'];
         }
         $data=Record::where($where);
         $data=$data->alias('a')
             ->join('erp2_user b','a.userid=b.userid')
             ->field('a.create_time,b_username,a.o_type,a.content');
         $data=$data->order('o_time','desc')->paginate($limit);
         $this->return_data(1,0,"",$data);

     }
}