<?php
/**
 * Created by PhpStorm.
 * User: ly
 * Date: 19-8-8
 * Time: 下午3:07
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
class Classes extends BaseController
{
    /*
     * 添加班级
     */
    public function addclasses()
    {   
        //$this->auth_get_token();
        $cls_id = input('cls_id');
        $stu_id = input('stu_id');
        $data = [
            'class_name'=>input('class_name'),
            'class_count'=>input('class_count'),
            'headmaster'=>input('headmaster'),
            'remarks'=>input('remarks'),
            'orgid' =>ret_session_name('orgid'),
            'manager' =>ret_session_name('uid'),
            'status'   =>1,
            'create_time' =>time(),
            'update_time' => time(),
        ];
         Db::startTrans();
         try{
        $validate = new \app\index\validate\Classes();
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }        
        $res = add('erp2_classes',$data,2);
         Db::commit();
        if($res){
        $data1 = [
            'cls_id'=>$cls_id,
            'cur_id'=>$res,
        ];
        $res1 = add('erp2_class_cur',$data1);
        if($res1){
        $aa = [];
        if(!empty($stu_id)){
            foreach ($stu_id as $k=>&$v){
                $aa['stu_id'] = $v;
                $aa['cls_id'] = $res;
            }
        }
        $res2 =  Db::name('erp2_class_student_relations')->insertAll($aa);
        if($res2){
            $this->return_data(1,0,'添加成功');
        }else{
            $this->return_data(0,10000,'操作失败');
        }
        }else{
            $this->return_data(0,10000,'操作失败');
        }
        }else{
            $this->return_data(0,10000,'操作失败');
        }
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    public function  classes_list()
    {  
       $where[] = ['status','=',1];
       $where[] = ['orgid','=',input('orgid')];
       $where[] = ['is_del','=',0];
       $class_name = input('class_name');
       $res = selects('erp2_classes',$where);
       foreach ($res as $k => &$v) {
          $v['headmasterinfo'] = finds('erp2_teachers',['t_id'=>$v['headmaster']]);
          $cls_id = $v['class_id'];
          $sql = "select c.cur_id , cu.cur_name from erp2_class_cur as c,erp2_curriculums as cu
           where c.cls_id={$cls_id}
           INNER JOIN c.cur_id=cu.cur_id";
          $v['curid'] = Db::query($sql);
       }
    }

    public  function  edit_classes()
    {
        $where['class_id'] = input('class_id');
        $data = [
            'class_name'=>input('class_name'),
            'headmaster'=>input('headmaster'),
            'class_count'=>input('class_count'),
            'remarks'=>input('remarks'),
        ];
        $res = edit('erp2_classes',$where,$data);
        if($res){
            $this->return_data(1,0,'修改成功');
        }else{
            $this->return_data(0,10000,'修改失败');
        }
    }

    public  function  edit_classes_curs()
    {
        $where['class_id'] = input('class_id');
        $stu_id = input('stu_id');
        $arr = [];
        foreach ($stu_id as $k=>$v)
        {
            $arr['stu_id'] = $v;
            $arr['class_id'] = input('class_id');
            $arr['is_del']  = 0;
        }
        $a = del('erp2_class_student_relations',$where);
        $b = Db::name('erp2_class_student_relations')->insertAll($arr);
        if($b){
            $this->return_data(1,0,'修改成功');
        }else{
            $this->return_data(0,10000,'修改失败');
        }
    }

    public  function  edit_curr()
    {
        $where['class_id'] = input('class_id');
        $data = ['cur_id','=',input('cur_id')];
        $res = edit('erp2_class_cur',$where,$data);
        if($res){
            $this->return_data(1,0,'修改成功');
        }else{
            $this->return_data(0,10000,'修改失败');
        }
    }

    public  function  del_classes()
    {
        $data['is_del'] = 1;
        $where['class_id'] = input('class_id');
        $res = edit('erp2_classes',$where,$data);
        if($res){
            $this->return_data(1,0,'修改成功');
        }else{
            $this->return_data(0,10000,'修改失败');
        }
    }




}