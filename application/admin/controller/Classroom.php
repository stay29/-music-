<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-7-22
 * Time: 下午3:08
 */
namespace app\admin\controller;
use think\Exception;

class Classroom extends AdminBase
{
    public function index()
    {
        $title = '教室列表';
        $this->assign('add', url('add'));
        $room_list = db('classrooms')->
        field('room_id, room_name, room_count, status, create_time, 
                     update_time, manager, or_id')->paginate(20, false, ['query' => request()->param()])->each(function ($v, $k) {
            if ($v['status'] == 1) {
                $v['status'] = '正常';
            } elseif ($v['status'] == 2) {
                $v['status'] = '已禁用';
            }
            $account = db('users')->where(['uid' => $v['manager']])->value('account');
            $v['manager'] = isset($account) ? $account : '系统';
            $v['org_name'] = db('organizations')->where('or_id', $v['or_id'])->value('or_name');
            return $v;
        });
        $this->assign('title', $title);
        $this->assign('room_list', $room_list);
        return $this->fetch();
    }

    public function edit()
    {
        $title = '教室编辑';
        if ($this->request->isGet()) {
            $map['room_id'] = input('room_id');
            $res = db('classrooms')->where($map)->field('room_id, room_name, 
                    room_count, or_id,status')->find();
            $org_list = db('organizations')->field('or_id, or_name')->select();
            $this->assign('org_list', $org_list);
            $this->assign('title', $title);
            $this->assign('res', $res);
            return $this->fetch();
        } else {
            $data = input();
            $map = input('room_id/d');
            $res = db('classrooms')->where($map)->update($data);
            if ($res) {
                $this->return_data(1, '编辑课程成功');
            } else {
                $this->return_data(0, '编辑课程失败');
            }
        }
    }

    /*
     * create classroom.
     */
    public function add()
    {
        $title = '添加教室';
        if ($this->request->isPost()) {

            $room_name = input('room_name');
            $room_count = input('room_count');
            $status = input('status');
            $org_id = input('org_id');
            $manager = session('admin.id');
            $data = [
                'room_name' => $room_name,
                'room_count' => $room_count,
                'status' => $status,
                'create_time' => time(),
                'update_time' => time(),
                'manager' => $manager,
                'or_id' => $org_id,
            ];
            $res = db('classrooms')->insertGetId($data);
            if ($res) {
                $this->return_data(1, '添加教室成功');
            } else {
                $this->return_data(0, '添加教室失败');
            }
        }
        $this->assign('title', $title);
        $org_list = db('organizations')->field('or_id, or_name')->select();
        $this->assign('org_list', $org_list);
        return $this->fetch();
    }

    /**
     * delete classroom
     */
    public function del()
    {
        $room_id = input('room_id');
        try {
            db('classrooms')->where('room_id', $room_id)->delete();
            $this->success('删除教室成功');
        } catch (Exception $e) {
            $this->error('删除教室失败');
        }
    }
}