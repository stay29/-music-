<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Students extends Controller
{

    /*
     * 学生列表
     */
    public function index()
    {
        $this->assign('title','学生列表');
        $students_list   = db('students')->paginate(20)->each(function($v,$k){
            if($v['status'] == 1){
                $v['status_text'] = '就读';
            }elseif($v['status'] == 2){
                $v['status_text'] = '离校';
            }
            $account = db('users')->where(['uid'=>$v['manager']])->value('account');
            $v['manager'] = isset($account) ? $account : '';
            return $v;
        });
        $this->assign('students_list',$students_list);
        return view();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
