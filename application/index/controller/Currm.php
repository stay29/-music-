<?php
namespace app\index\controller; 
use think\Controller;
use think\Exception;
use app\index\model\Curriculums;
class Currm extends BaseController
{
    //课程列表
    public function index()
    {
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
        $where[]=['orgid','=',$orgid];
        $where[] = ['is_del','=',0];
        try{
        $res = Curriculums::getall($limit,$where);
        $this->return_data(1,0,$res);
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }
    //添加课程
 	public function  addcurrmon()
    {
 		$data = input('post.');
 		$data['manager'] = session(md5(MA.'user'))['id'];
        $validate = new \app\validate\Curriculums;
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try{
            $res = Curriculums::addcurrl($data);
            $this->return_data(1,0,'添加成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
 	}
    //修改课程
    public function editcurrm()
    {
        $currid = input('post.cur_id');
        $data = input('post.');
        $validate = new \app\validate\Curriculums;
        if(!$validate->scene('edit')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try{
            $res = Curriculums::editcurrm($currid,$data);
            $this->return_data(1,0,'修改成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }


    //删除课程
    public function delcurrmon()
    {
        $where['cur_id'] = input('cur_id');
        $data['is_del'] = 1;
        if($where==null){
            $this->return_data(0,10000,'缺少参数');
        }
        $res = Curriculums::delcurrl($where,$data);
        if($res){
        $this->return_data(1,0,'删除成功');
        }else{
        $this->return_data(0,20003,'操作失败');
        }
    }

    //获取单个课程
    public function getcurrm()
    {
        $currid['cur_id'] =   input('cur_id');
        $res = Curriculums::getcurrmone($currid);
        $this->return_data(1,0,$res);
    }

    //设置热门 1为热门 2为取消热门
    public function  edit_popular(){
        $currid  =   input('cur_id');
        $where['orgid'] = session(md5(MA.'user'))['orgid'];
        $data2['popular'] = 2;
        $res =  Curriculums::where($where)->update($data2);
        foreach ($currid as $k=>&$v){
            $cid = $v;
            $data['popular'] = 1;
            $res = Curriculums::where('cur_id',$cid)->update($data);
        }
        if($res){
            $this->return_data(1,0,'设置成功');
        }else{
            $this->return_data(0,50000,'设置失败');
        }
    }

    //上传图片
    public function get_img_update()
    {
        $res =  $this->get_ret_img_update('img','./upload/currm/');
        $imgpath = './upload/currm/'.$res;
        $this->return_data(1,0,$imgpath);
    }

    //删除图片
    public  function  get_img_del()
    {
        $oldig = input('oldimg');
        $res = file_exists($oldig);
        if($res){
            unlink($oldig);
            $this->return_data(1,0,'删除成功');
        }else{
            $this->return_data(0,50000,'删除失败');
        }
    }
    //全部搜索课程列表
    public  function  all_list_currm()
    {
        $cur_name = input('cur_name');
        $orgid  =  input('orgid');
        $where = null;
        if($cur_name){
            $where[]=['cur_name','like','%'.$cur_name.'%'];
        }
        if($orgid){
            $where[]=['orgid','=', $orgid];
        }
        $where[]=['is_del','=', 0];
        try{
            $res = Curriculums::get_all($where);
            //print_r($res);exit();
            $this->return_data(1,0,'返回成功',$res);
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }
}