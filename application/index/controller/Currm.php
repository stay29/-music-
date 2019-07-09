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
 		$validate = new \app\validate\Curriculums;
 		if (!$validate->check($data)) {
        	return$validate->getError();
    	}

    	
 	}
}
