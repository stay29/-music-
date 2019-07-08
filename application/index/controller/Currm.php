<?php
namespace app\index\controller; 
use think\Controller;
use think\Exception;
use app\index\model\Curriculums;
class Currm extends controller
{ 
    public function index()
    {
        $Curriculums = new Curriculums;
      return  $res = $Curriculums->get_curriculums();
        //print_r($res);
         //return view();
    }
   
 
}
