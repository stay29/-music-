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
        ];
        //print_r($data);exit();
        $uid = input('uid');
        $validate = new \app\index\validate\Organization();
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
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
            $this->return_data(1,0,$userinfo);
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

}