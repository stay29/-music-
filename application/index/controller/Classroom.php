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
use PHPExcel;
use think\Exception;
use think\Log;


class Classroom extends BaseController
{
    /**
     * Show classroom information list.
     */
    public function index()
    {
        $oid = input('orgid', '');
        if(empty($oid))
        {
            $this->return_data(1, '', '', array());
        }
        $limit = input('limit/d', 20);
        $status = input('status/d', null);
        $room_name = input('name/s', null);

        $where[] = ['or_id', '=', $oid];

        if(isset($status) and ($status==2 || $status==1) and !empty($status))
        {
            $where[] = ['status', '=', $status];
        }
        if(!empty($room_name))
        {
            $where[] = ['room_name', 'like', '%' . $room_name. '%'];
        }
        $where[] = ['is_del', '=', 0];
        $res = ClsModel::where($where)->field('room_id as id,room_name as 
            name,status,room_count as total')->paginate($limit);
        $this->return_data(1, 0, '', $res);
    }

    /**
     * Adding Classroom Interface
     */
    public function add(){
        $oid = input('orgid', '');
        if (empty($oid))
        {
            $this->return_data(0, '10000', '缺少参数');
        }

        $data = [
            'room_name' => input('post.name'),
            'status' => input('post.status'),
            'room_count' => input('post.total'),
            'or_id' => $oid,
            'is_del' => 0
        ];

        $validate = new \app\index\validate\Classroom();

        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try{
            ClsModel::create($data)->save();
            $this->return_data(1,0,'教室新增成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    /**
     * Edit classroom's information.
     */
    public function edit(){
        $oid = input('orgid', '');
        if (empty($oid))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
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
            'or_id' => $oid,
            'room_id' => $data['room_id']
        ];
        try{
            ClsModel::where($where)->update($data);
            $this->return_data(1,0,'教室编辑成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    /**
     * delete classroom information.
     */
    public function del(){
        $id = input('id/d');
        $oid = ret_session_name('orgid');

        if(empty($id)){
            $this->return_data(0,10000,'缺少教室ID');
        }

        $where[] = ['room_id', '=', $id];
        $where[] = ['or_id', '=', $oid];
        try
        {
            ClsModel::where($where)->update(['is_del'=>1]);
            $this->return_data(1,0,'删除教室成功');
        }catch (Exception $e){
            $this->return_data(0,20003,'删除失败');
        }
    }
}


