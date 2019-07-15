<?php
namespace app\index\controller; 
use think\Controller;
use app\index\model\Subjects;
class SubjectsInfo extends controller
{ 			
    public function index()
    {
        $res = Subjects::getall();
        $state['state'] = true;
        $state['msg'] = '';
        $state['data'] = $res;
        return json_encode($state);
    }
}