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


class Classroom extends BaseController
{
    /**
     * 获取教室列表
     */
    public function index()
    {
        $oid = ret_session_name('orgid');
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
        $res = ClsModel::where($where)->field('room_id as id,room_name as 
            name,status,room_count as total')->paginate($limit);

        $this->return_data(1, 0, '', $res);
    }

    /**
     * 添加教室
     */
    public function add(){
        $oid = ret_session_name('orgid');
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

    /**
     * 修改教室
     */
    public function edit(){
        $oid = ret_session_name('orgid');
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
     * 删除教室
     */
    public function del(){
        $id = input('id/d');
        $oid = ret_session_name('orgid');

        if(empty($id)){
            $this->return_data(0,10000,'缺少教室主键');
        }
        $where = [
            'room_id' => $id,
            'or_id' => $oid
        ];
        $res = ClsModel::where($where)->delete();
        if($res){
            $this->return_data(1,0,'删除教室成功');
        }else{
            $this->return_data(0,20003,'删除失败');
        }
    }


    /*
     * 教室数据导出
     */
    public function export()
    {

    }

    /*
     * 教室数据导入
     */
    public function import()
    {

    }

}
