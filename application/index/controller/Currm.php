<?php
namespace app\index\controller; 
use think\Controller;
use think\Exception;
use app\index\model\Curriculums;

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
class Currm extends BaseController
{

    public function index()
    {
        $this->auth_get_token();
        $page = input('page');
        if($page==null){
          $page = 1;
        }
        $limit = input('limit');
        if ($limit==null) {
        $limit = 6;
        }
        $cur_name = input('cur_name');
        $subject = input('subject');
        $tmethods = input('tmethods');
        $status = input('status');
        $orgid  = input('orgid');
        if(!$orgid){
            $orgid = ret_session_name('orgid');
        }
        $where = null;
        if($cur_name){
            $where[]=['cur_name','like','%'.$cur_name.'%'];
        }
        if($subject){
            $where[]=['subject','=',$subject];
        }
        if($tmethods){
            $where[]=['tmethods','=',$tmethods];
        }
        if($status){
            $where[]=['status','=', $status];
        }
        $where[] = ["is_del",'=',"0"];
        $where[] = ["orgid",'=',"$orgid"];
        try{
        $res = Curriculums::getall($limit,$where);
//        $this->return_data(1,0,$res);
        $this->returnData($res, '请求成功');
        }catch (\Exception $e){
            $this->returnError(50000, $e->getMessage());
//            $this->return_data(0,50000,$e->getMessage());
        }
    }

    //添加课程
 	public function  addcurrmon()
    {
        $this->auth_get_token();
 		$data = input('post.');
 		$data['manager'] = ret_session_name('uid');
 		if (empty($data['orgid']))
        {
            $this->returnError(10000, '缺少ordid.');
        }
        $validate = new \app\validate\Curriculums;
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->returnError($error[1], $error[0]);
//            $this->return_data(0,$error[1],$error[0]);
        }
        try{
            $res = Curriculums::addcurrl($data);
            $this->returnData($res, '添加成功');
//            $this->return_data(1,0,'添加成功');
        }catch (\Exception $e){
            $this->returnError(50000, $e->getMessage());
//            $this->return_data(0,50000,$e->getMessage());
        }
 	}

    //修改课程
    public function editcurrm()
    {
        $this->auth_get_token();
        $currid = input('post.cur_id');
        $data = input('post.');
        $validate = new \app\validate\Curriculums;
        if(!$validate->scene('edit')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
//            $this->return_data(0,$error[1],$error[0]);
            $this->returnError($error[1], $error[0]);
        }
        try{
            $res = Curriculums::editcurrm($currid,$data);
            $this->returnData($res, '修改成功');
//            $this->return_data(1,0,'修改成功');
        }catch (\Exception $e){
            $this->returnError(50000, $e->getMessage());
//            $this->return_data(0,50000,$e->getMessage());
        }
    }


    //删除课程
    public function delcurrmon()
    {
        $this->auth_get_token();
        $where['cur_id'] = input('cur_id');
        $data['is_del'] = 1;
        if($where==null){
            $this->returnError(10000, '缺少参数');
//            $this->return_data(0,10000,'缺少参数');
        }
        $res = Curriculums::delcurrl($where,$data);
        if($res){
//        $this->return_data(1,0,'删除成功');
        $this->returnData($res, '删除成功');
        }else{
            $this->returnError(20003, '操作失败');
//        $this->return_data(0,20003,'操作失败');
        }
    }


    //获取单个课程
    public function getcurrm()
    {
        $currid['cur_id'] =   input('cur_id');
        $res = Curriculums::getcurrmone($currid);
//        $this->return_data(1,0,$res);
        $this->returnData($res, '操作成功');
    }


    //设置热门 1为热门 2为取消热门
    public function  edit_popular(){
        $this->auth_get_token();
        $currid  =   input('cur_id');
        if(empty($currid)){
//            $this->returnError(10000, '缺少参数');
//               $this->return_data(0,10000,'请选中数据在提交');
        }
        $where['orgid'] = session(md5(MA.'user'))['orgid'];
        $data2['popular'] = 2;
        $res =  Curriculums::where($where)->update($data2);
        foreach ($currid as $k=>&$v){
            $cid = $v;
            $data['popular'] = 1;
            $res = Curriculums::where('cur_id',$cid)->update($data);
        }
        if($res){
            $this->returnData(1, '设置成功');
//            $this->return_data(1,0,'设置成功');
        }else{
//            $this->return_data(0,50000,'设置失败');
            $this->returnError(50000, '删除失败');
        }
    }


    //上传图片
    public function get_img_update()
    {
        $res =  $this->get_ret_img_update('img','./upload/currm/');
        $imgpath = './upload/currm/'.$res;
        $this->returnData($imgpath, '');
    }

    //删除图片
    public  function  get_img_del()
    {
        $oldig = input('oldimg');
        $res = file_exists($oldig);
        if($res){
            unlink($oldig);
            $this->returnData(1,'删除成功');
        }else{
            $this->returnError(50000,'删除失败');
        }
    }
    //全部搜索课程列表
    public  function  all_list_currm()
    {
        $this->auth_get_token();
        $cur_name = input('cur_name');
        $orgid  =  input('orgid');
        $where = null;
        if($cur_name != null){
            $where[]=['cur_name','like','%'.$cur_name.'%'];
        }
        $where[]=['orgid','=', $orgid];
        $where[]=['is_del','=', 0];
        try{
            $res = Curriculums::get_all($where);
            //print_r($res);exit();
//            $response = [
//                'data' => $res
//            ];
            $this->returnData($res, '操作成功');
//            $this->return_data(1,0,'返回成功',$res);
        }catch (\Exception $e){
            $this->returnError(50000, $e->getMessage());
//            $this->return_data(0,50000,$e->getMessage());
        }
    }
}