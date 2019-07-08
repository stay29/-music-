<?php
namespace app\admin\controller; 
use think\Exception;
class Organ extends AdminBase
{
   

    public function index()
    {
       
       $this->assign('title','课程列表');
       //$this->assign('add',url('add'));
       $users_list   = db('curriculums')->paginate(20)->each(function($v,&$k){
            if($v['state'] == 1){
                $v['state'] = '上架';
            }elseif($v['state'] == 2){
                $v['state'] = '下架';
            }
            $admins =  db('admins')->where(['id'=>$v['manager']])->value('ad_account');
            if($admins){
                $v['manager'] = $admins;
            }
            return $v;
        });
       $this->assign('users_list',$users_list);
        return view();
    }
   
 
}
