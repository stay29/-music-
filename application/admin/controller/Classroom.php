<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-7-22
 * Time: 下午3:08
 */

namespace app\admin\controller;


class Classroom extends AdminBase
{
    public function index()
    {
        $title = '教室列表';
        $room_list = db('classrooms')->
                    field('room_id, room_name, room_count, status, create_time, 
                     update_time, manager')->paginate(20, false, ['query'=>request()->param()])->each(function($v, $k){
            if($v['status'] == 1){
                $v['status'] = '正常';
            }elseif($v['status'] == 2){
                $v['status'] = '已禁用';
            }
            $account = db('users')->where(['uid'=>$v['manager']])->value('account');
            $v['manager'] = isset($account) ? $account : '系统';
            return $v;
        });
        $this->assign('title', $title);
        $this->assign('room_list', $room_list);
        return $this->fetch();
    }

    public function edit()
    {
        $title = '教室编辑';
        if ($this->request->isGet())
        {
            $map['room_id'] = input('room_id');
            $res = db('classrooms')->where($map)->field('room_id, room_name, 
                    room_count, status')->find();
            $this->assign('title', $title);
            $this->assign('res', $res);
            return $this->fetch();
        }
        else
        {
            $data = input();
            $map = input('room_id/d');
            $res = db('classrooms')->where($map)->update($data);
            if ($res)
            {
                $this->return_data(1,'编辑课程成功');
            }
            else
            {
                $this->return_data(0, '编辑课程失败');
            }
        }
    }

    /*
     * create classroom.
     */
    public function add()
    {

    }

    /**
     * delete classroom
     */
    public function del()
    {

    }


}