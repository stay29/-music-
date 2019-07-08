<?php
namespace app\admin\controller; 
use think\Exception;
class Organization extends AdminBase
{
    public function index()
    {
       
       $this->assign('title','机构列表');
       //$this->assign('add',url('add'));
       $org_list   = db('organizations')->paginate(20);
       $this->assign('org_list',$org_list);
        return view();
    }
}