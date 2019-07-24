<?php
/**
 * 教室
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */
namespace app\index\controller;


class Classroom extends BaseController
{
    /**
     * classroom list info.
     */
    public function index()
    {
        $uid = $this->user['uid'];
        $oid = $this->user['organization'];
        $sql = "SELECT ";
    }

    /**
     * insert classroom info.
     */
    public function add()
    {

    }

    /*
     * edit classroom info.
     */
    public function edit()
    {

    }

    public function del()
    {

    }
}
//
//class Classroom extends BaseController
//{
//    /**
//     * 我的查询，用于搜索
//     */
//    protected function _where($model){
//        if(!$model){
//            return '';
//        }
//        $room_name = input('get.classroom');
//        $status = input('get.status/d');
//        $status?$model->where('status',$status):'';
//        $room_name?$model->whereLike('room_name','%'.$room_name.'%'):'';
//        return $model;
//    }
//
//    /**
//     * 教室列表
//     */
//    public function get(){
//       $model = \app\index\model\Classroom
//           ::field('room_id as id,room_name as name,status,room_count as total')
//            ->order('create_time desc');
//       $res = $this->_where($model)->paginate(20);
//       $this->return_data(1,0,'',$res);
//    }
//
//    /**
//     * 新增教室
//     */
//    public function add(){
//        $data = [
//            'room_name' => input('post.name'),
//            'status' => input('post.status'),
//            'room_count' => input('post.total'),
//        ];
//
//        $validate = new \app\index\validate\Classroom();
//        if(!$validate->scene('add')->check($data)){
//            //为了可以得到错误码
//            $error = explode('|',$validate->getError());
//            $this->return_data(0,$error[1],$error[0]);
//        }
//
//        try{
//           \app\index\model\Classroom::create($data);
//           $this->return_data(1,0,'教室新增成功');
//        }catch (\Exception $e){
//            $this->return_data(0,50000,$e->getMessage());
//        }
//    }
//    /**
//     * 编辑教室
//     */
//    public function edit(){
//        $data = [
//            'room_id'=>input('post.id'),
//            'room_name' => input('post.name'),
//            'status' => input('post.status'),
//            'room_count' => input('post.total'),
//        ];
//
//        $validate = new \app\index\validate\Classroom();
//        if(!$validate->scene('edit')->check($data)){
//            //为了可以得到错误码
//            $error = explode('|',$validate->getError());
//            $this->return_data(0,$error[1],$error[0]);
//        }
//
//        try{
//            \app\index\model\Classroom::where(['room_id'=>$data['room_id']])->update($data);
//            $this->return_data(1,0,'教室编辑成功');
//        }catch (\Exception $e){
//            $this->return_data(0,50000,$e->getMessage());
//        }
//    }
//
//    /**
//     * 删除教室 硬删除
//     */
//    public function del(){
//        $id = input('id/d');
//        if(empty($id)){
//            $this->return_data(0,10000,'缺少教室主键');
//        }
//        $res = \app\index\model\Classroom::where('room_id',$id)->delete();
//        if($res){
//            $this->return_data(1,0,'删除教室成功');
//        }else{
//            $this->return_data(0,20003,'删除失败');
//        }
//    }
//}