<?php
/**
 * 教室
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */
namespace app\index\controller;

use app\index\model\Classroom as ClsModel;
use think\Controller;

class Classroom extends BaseController
{

    public function index()
    {
        $oid = $this->user['organizations'];
        $status = input('status/d', null);
        $where = [
            'or_id' => $oid,
        ];
        $room_name = input('name', null);
        if(isset($status))
        {
            $where['status'] = $status;
        }
        if(isset($room_name))
        {
            $where['room_name'] = $room_name;
        }
        $res = ClsModel::field('room_id as id,room_name as name,status,room_count as total')
            ->order('create_time desc')->where($where)->paginate(20);
        $this->return_data(1, 0, '', $res);
    }

    public function add(){
        $oid = $this->user['organizations'];
        $data = [
            'room_name' => input('post.name'),
            'status' => input('post.status'),
            'room_count' => input('post.total'),
            'oid' => $oid
        ];
        $validate = new \app\index\validate\Classroom();

        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try{
            ClsModel::create($data);
            $this->return_data(1,0,'教室新增成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    public function edit(){
        $oid = $this->user['organizations'];
        $data = [
            'room_id'=>input('post.id'),
            'room_name' => input('post.name'),
            'status' => input('post.status'),
            'room_count' => input('post.total'),
        ];
        $validate = new \app\index\validate\Classroom();
        if(!$validate->scene('edit')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        $where = [
            'oid' => $oid,
            'room_id' => $data['room_id']
        ];
        try{
            ClsModel::where($where)->update($data);
            $this->return_data(1,0,'教室编辑成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    public function del(){
        $id = input('id/d');
        $oid = $this->user['organizations'];

        if(empty($id)){
            $this->return_data(0,10000,'缺少教室主键');
        }
        $where = [
            'room_id' => $id,
            'oid' => $oid
        ];
        $res = ClsModel::where($where)->delete();
        if($res){
            $this->return_data(1,0,'删除教室成功');
        }else{
            $this->return_data(0,20003,'删除失败');
        }
    }

}
