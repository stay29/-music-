<?php
namespace app\index\controller; 
use think\Controller;
use think\Exception;
use app\index\model\Curriculums;
use app\index\Validate;
class Currm extends controller
{ 	

		
    public function index()
    {
         $Curriculums = new Curriculums;
         $res = $Curriculums->get_curriculums();
         return  json_encode($res);
    }
   

    public function addcurrm(){


    	return view();
    }
 	public function  addcurrmon(){
 		$data = input('post.');
 		//print_r($data);exit();
 		$validate = new \app\validate\Curriculums;
 		$Curriculums = new Curriculums;
 		if (!$validate->check($data)) {
        	return$validate->getError();
    	}
    	$res = $Curriculums->addcurrl($data);
    	if($res){
    		return json_encode('添加成功');
    	}else{
    		return json_encode('添加失败');
    	}
 	}

    public function delcurrmon(){
        $data['cur_id'] = input('post.cur_id');
        $Curriculums = new Curriculums;
        $res = $Curriculums->delcurrl($data);
        print_r($res);
    }

    public function editcurrm(){
        $currid['cur_id'] =   input('post.cur_id');
        $data = input('post.');
        $Curriculums = new Curriculums;
        $res = $Curriculums->editcurrm($currid,$data);
        print_r($res);
    }

    public function getcurrm(){
        $currid['cur_id'] =   input('post.cur_id');
        $Curriculums = new Curriculums;
        $res = $Curriculums->getcurrmone($currid);
        echo json_encode($res);
    }

}
