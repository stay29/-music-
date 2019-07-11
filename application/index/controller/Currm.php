<?php
namespace app\index\controller; 
use think\Controller;
use think\Exception;
use app\index\model\Curriculums;
class Currm extends controller
{ 			
    public function index()
    {
        $page = input('page');
        if($page==null){
          $page = 1;  
        }
        $limit = input('limit');
        if ($limit==null) {
        $limit = 1;
        }
        $res = Curriculums::getall($limit);
        $count = $res->total();
        //$pages = $res->render();
        $state['state'] = true;
        $state['msg'] = '';
        $state['data'] = $res;
        echo json_encode($state);
    }
    
    public function addcurrm(){
        return view();
    }

 	public function  addcurrmon(){
 		$data = input('post.');
        $validate = new \app\validate\Curriculums;
        if (!$validate->check($data)) {
            echo($validate->getError());die;
        }
    	$res = Curriculums::addcurrl($data);
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
        $validate = Validate::make([
             'cur_name|课程名称'=>[
                'require',
                'min'=>1,
                'max'=>2,
            ],
            'subject|课程科目'=>[
                'require',
            ],
            'describe|课程描述'=>[
                'require',
                'min'=>1,
                'max'=>200,
            ],
            'remarks|备注'=>[
                'require',
                'min'=>1,
                'max'=>200,
            ],
            'ctime|课时'=>[
                'require',
                'min'=>1,
            ],
        ]);
        if (!$validate->check($data)) {
            dump($validate->getError());die;
        }
        $res = Curriculums::editcurrm($currid,$data);
        print_r($res);
    }


    public function getcurrm(){
        $currid['cur_id'] =   input('post.cur_id');
        $Curriculums = new Curriculums;
        $res = $Curriculums->getcurrmone($currid);
        echo json_encode($res);
    }

}
