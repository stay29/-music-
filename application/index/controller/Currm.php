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
        //print_r($res);exit();
        $this->return_data(1,0,$res);
    }


    public function addcurrm()
    {
        return view();
    }

 	public function  addcurrmon()
    {

 		$data = input('post.');
        $validate = new \app\validate\Curriculums;
        if (!$validate->check($data)) {
            $this->return_data(0,0,$validate->getError());
        }
    	$res = Curriculums::addcurrl($data);
        //print_r($res);exit();
    	if($res){
        $this->return_data(1,0,'添加成功');
    	}else{
    	$this->return_data(1,0,'操作失败');
    	}
 	}

    public function delcurrmon()
    {
        $data['cur_id'] = input('cur_id');
        if($data==null){
            $this->return_data(0,0,'操作失败');
        }
        $Curriculums = new Curriculums;
        $res = $Curriculums->delcurrl($data);
        if($res){
        $this->return_data(1,0,$res);
        }else{
        $this->return_data(0,0,'操作失败');
        }
    }
    public  function  editcurrmvie(){
        $currid['cur_id'] =   input('cur_id');
        $res =  Curriculums::where($currid)->find();
        $this->assign('res',$res);
        return view();
    }
    public function editcurrm()
    {
        $currid = input('post.cur_id');
        $data = input('post.');
        //print_r($data);exit();
           $validate = new \app\validate\Curriculums;
        if (!$validate->check($data)) {
            $this->return_data(0,0,$validate->getError());
        }
        $res = Curriculums::editcurrm($currid,$data);
        if($res){
        $this->return_data(1,0,'操作成功');
        }else{
        $this->return_data(0,0,'操作失败');
        }
    }

    public function getcurrm()
    {
        $currid['cur_id'] =   input('cur_id');
        $res = Curriculums::getcurrmone($currid);
        $this->return_data(1,0,$res);
    }

}
