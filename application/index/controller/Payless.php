<?php
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
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/26
 * Time: 13:36
 */
namespace app\index\controller;
use think\Collection;
use think\Db;
use app\index\model\PayList;
use app\validate\PayList as pays;//同名会引起报错 启用别名
use app\index\model\Curriculums;
use app\index\model\PayInfo as payinfos;
use app\index\model\Organization as Organ;
use app\index\model\Users;
    class Payless extends  BaseController
{
//添加课程薪酬
public function  addpayinfo()
{
    //接受数据
    $cur_id   =    input('cur_id');
    $data = [
        'pless_id'   =>   input('pless_id'),
        'p_id'   =>   input('p_id'),
        'remake'   =>   input('remake'),
        'bay_paich'   =>   input('bay_paich'),
        'orgid'   =>   input('orgid'),
        'manager'   =>   ret_session_name('uid'),
    ];

    $arr = array();
    $arr1 = array();
    //数据处理
    foreach ($cur_id as $k=>&$v)
    {
        $cur_name = Curriculums::where('cur_id',$v)->find();
        $arr['cur_name'] = $cur_name['cur_name'];
        $arr['pless_id'] = input('pless_id');
        $arr['p_id'] = input('p_id');
        $arr['remake'] = input('remake');
        $arr['bay_paich'] = input('bay_paich');
        $arr['orgid'] = input('orgid');
        $arr['manager'] = input('manager');
        $arr['cur_id'] = $v;
        $arr1[] = $arr;
    }
    Db::startTrans();
    try {
        $validate = new pays;
       foreach ($arr1 as $ks=>$vs)
       {
           //判断是不是已经设置
           $where['cur_id'] = $vs['cur_id'];
           $where['orgid'] = input('orgid');
           $where['is_del'] = 0;
           $oginfo =  PayList::where($where)->find();
           if($oginfo){
               $this->return_data(0,'20000 ','该课程已经设置');
           }
           //验证器
           if(!$validate->scene('add')->check($vs)){
               $error = explode('|',$validate->getError());
               $this->return_data(0,$error[1],$error[0]);
           }else{
               $res = PayList::create($vs);
               // 提交事务
               Db::commit();
           }
       }
        if($res){
            $this->return_data(1,0,'添加成功');
        }else{
            $this->return_data(0,10000,'添加失败');
        }
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        $this->return_data(0,50000,$e->getMessage());
    }
}

//删除课程薪酬
public  function  del_pay_list()
{
    $data = [
        'pay_id_list'   =>   input('pay_id_list'),
        'orgid'   =>   input('orgid'),
        //'manager'   =>   input('uid'),
    ];
    $up['is_del'] = 1;
    $validate = new pays;
    if(!$validate->scene('del')->check($data)) {
        $error = explode('|',$validate->getError());
        $this->return_data(0,$error[1],$error[0]);
    }else {
        $res = PayList::where($data)->update($up);
        if($res){
            $this->return_data(1,0,'删除成功',$res);
        }else{
            $this->return_data(0,10000,'删除失败',$res);
        }
    }
}

//薪酬列表
public  function  pay_list()
{
    $page = input('page');
    if($page==null){
        $page = 1;
    }
    $limit = input('limit');
    if($limit==null){
        $limit = 10;
    }
    $subject = input('subject');
    $cur_name = input('cur_name');
    $where = array();
    if($cur_name!=null){
        $where[]=['cur_name','like','%'.$cur_name.'%'];
    }
    $orgid = input('orgid');
    $where[] = ['is_del','=',0];
    $where[] = ['orgid','=',$orgid];

    $res = Db::table('erp2_pay_list')->where([$where])->select();
    foreach ($res as $k=>&$v){
        $v['curriculums'] = db('curriculums')->where('cur_id',$v['cur_id'])->find();
        $v['subjectsall'] = db('subjects')->where('sid',$v['curriculums']['subject'])->find();                                     $v['pay_info'] = db('pay_info')->where('pay_id_info',$v['p_id'])->find();
    }
    $res1 = array();
    if($subject!=0){
        foreach ($res as $ks=>&$vs){
            if($vs['subjectsall']['sid']==$subject){
                $res1[] = $vs;
            }
        }
    }else{
           $res1 = $res;
    }
    $res_list = $this->array_page_list_show($limit,$page,$res1,1);
    $this->return_data(1,0,'搜索成功',$res_list);
    }


    //数组分页方法
    public function array_page_list_show($count,$page,$array,$order)
    {
        $page=(empty($page))?'1':$page; #判断当前页面是否为空 如果为空就表示为第一页面
        $start=($page-1)*$count; #计算每次分页的开始位置
        if($order==1){
            $array=array_reverse($array);
        }
        $pagedata=array();
        $pagedata['limit'] = $count;
        $pagedata['countarr'] = count($array);
        $pagedata['to_pages'] = ceil(count($array)/$count);
        $pagedata['page'] = $page;
        $pagedata['data']=array_slice($array,$start,$count);    //分隔数组
        return $pagedata;  #返回查询数据
    }


    //修改课程薪酬
    public  function  edit_pay_list()
    {
        $pay_id_list   =   input('pay_id_list');
        $data = input('post.');
        //print_r($data);exit();
        $res =  PayList::where('pay_id_list',$pay_id_list)->update($data);
        if($res){
            $this->return_data(1,0,'修改成功');
        }else{
            $this->return_data(0,10000,'信息没有变动');
        }
    }

    //课程薪酬计算方式列表
    public  function  pay_info_list()
    {
        //$where['orgid'] = input('orgid');
        $where['status'] = 1;
        $pay_id_info = input('pay_id_info');
        if($pay_id_info){
        $where['pay_id_info'] = $pay_id_info;
        }
        $res = payinfos::where($where)->select();
        $this->return_data(1,0,'查询成功',$res);
    }

    //获取单条课程薪酬
    public  function  get_pay_list_info(){
        $pay_id_list   =   input('pay_id_list');
        $res =  PayList::where('pay_id_list',$pay_id_list)
            ->alias('p')
            ->leftJoin('erp2_curriculums c','p.cur_id= c.cur_id')
            ->join('erp2_subjects s','c.subject=s.sid')
            ->find();
        $this->return_data(1,0,'查询成功',$res);
    }

    //课程薪酬设置
    public  function  edit_pay_state()
    {
        $orgid = input('orgid');
        $where['pay_state'] = input('pay_state');
        $res =Organ::where('or_id',$orgid)->update($where);
        $uid = ret_session_name('uid');
        $arr = Users::loginsession($uid);
        if($res){
            $this->return_data(1,0,'操作成功',$arr);
        }else{
            $this->return_data(0,10000,'操作失败',$res);
        }
    }

    //薪酬课程列表
    public  function  get_curr_pay_list()
    {
        $where = array();
        $orgid = input('orgid');
        $where[] = ['is_del','=',0];
        $where[] =  ['orgid','=',$orgid];
        $list = PayList::where($where)->field('cur_id')->select();
        $arr1 = array();
        foreach ($list as $k=>$v) {
            $arr1[] = $v['cur_id'];
            $where[] = ['cur_id','<>',$v['cur_id']];
        }
        $res =  Curriculums::where($where)->select();
        foreach ($res as $k1=>$v1)
        {
            $v1['state_in'] = false;
        }
        $this->return_data(1,0,'查询成功',$res);
    }
}