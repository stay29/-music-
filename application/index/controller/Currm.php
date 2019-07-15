<?php
namespace app\index\controller; 
use think\Controller;
use think\Exception;

use app\index\model\Curriculums;
class Currm extends BaseController
{ 			
    public function index()
    {
        $page = input('page');
        if($page==null){
          $page = 1;  
        }
        $limit = input('limit');
        if ($limit==null) {
        $limit = 10;
        }
        $res = Curriculums::getall($limit);
        $this->return_data(1,0,$res);
    }

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

    public function delcurrmon()
    {
        $data['cur_id'] = input('cur_id');
        if($data==null){
            $this->return_data(0,10000,'缺少参数');
        }
        $res = Curriculums::delcurrl($data);
        if($res){
        $this->return_data(1,0,'删除成功');
        }else{
        $this->return_data(0,20003,'操作失败');
        }
    }


    public  function  editcurrmvie(){
        $currid['cur_id'] =   input('cur_id');
        $res =  Curriculums::where($currid)->find();
        $this->assign('res',$res);
        return view();
    }

    public function getcurrm()
    {
        $currid['cur_id'] =   input('cur_id');
        $res = Curriculums::getcurrmone($currid);
        $this->return_data(1,0,$res);
    }


    public function get_img_update()
    {
      $res =  $this->get_ret_img_update('img','./upload/currm/');
      return $res;
    }


    public  function  get_img_del()
    {
        $oldig = input('oldimg');
        $res = file_exists($oldig);
            if($res){
                unlink($oldig);
                return true;
            }else{
                return false;
            }
    }
}
