<?php
namespace app\index\controller; 
use think\Controller;
use think\Exception;
use app\index\model\Curriculums;
class Currm extends controller
{ 
    public function index()
    {
       
        $res = Curriculums::select();
        $this->assign('res',$res);
        return view();
    }
   
 
}
