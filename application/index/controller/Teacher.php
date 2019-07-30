<?php
/**
 * 教师
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */

namespace app\index\controller;
use app\admin\controller\AdminBase;
use app\index\model\Teacher as TeacherModel;
use think\Controller;
use think\Db;
use think\Exception;
use think\Log;

class Teacher extends BaseController
{
    /*
     * 教师列表
     */
    public function index()
    {
        $t_name = input('t_name/s', null); // 教师名称
        $se_id = input('se_id/s', null); // 资历ID
        $status = input('status/d', null);  // 离职状态
        $where = array();
        if(!empty($t_name))
        {
            $where[] = ['t_name', 'like', '%' . $t_name. '%'];
        }
        if(!empty($status))
        {
            $where[] = ['status', '=', $status];
        }
        if(!empty($se_id))
        {
            $where = ['se_id', '=', $se_id];
        }
        $teacher = TeacherModel::where($where)->field('t_id as id,t_name as name,
                sex,cellphone,birthday,entry_time,status, se_id, resume')->paginate(20);
        $this->return_data(1, '','', $teacher);
    }

    /*
     * 修改教师信息
     */
    public function edit(){
        $t_id = input('post.id');
        $data = [
            't_id'=>$t_id,
            't_name' => input('post.name'),
            'avator' => input('post.avator'),
            'sex' => input('post.sex',1),
            'se_id' => input('post.se_id'),
            'cellphone' => input('post.cellphone'),
            'birthday' => input('post.birthday'),
            'entry_time' => input('post.entry_day'),
            'resume' => input('post.resume'),
            'identity_card' => input('post.id_card')
        ];
        $validate = new \app\index\validate\Teacher();
        if (!$validate->check($data)) {
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try{
            \app\index\model\Teacher::update($data,['t_id'=>$data['t_id']]);
            $this->return_data(1,0,'编辑教师成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    /*
     * 删除教师
     */
    public function del()
    {
        $t_id = input('t_id/d', null);
        if(empty($t_id))
        {
            $this->return_data(0, '10000', '缺少t_id');
        }
        try {

            $res = db('teachers')->where('t_id', '=', $t_id)->delete();
            if($res)
            {
                $this->return_data(1, '', '', '删除教师成功');
            }
            else
            {
                $this->return_data(0, '20003', '删除教师失败');
            }
        }catch (Exception $e)
        {
            $this->return_data(0, '20003', '删除教师失败');
        }
    }

    /**
     * 教师详情
     */
    public function detail()
    {
        $org_id = input('orgid', null);
        $t_id = input('tid', null);
        if (!isset($org_id) || !isset($t_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        // 查询教师详情信息
        $teacher_sql = "SELECT seniority_id as se_id, seniority_name as se_name,
	                A.t_id as tid, A.t_name as name, A.sex, A.cellphone, A.birthday, A.entry_time, A.status,  A.resume
                    FROM erp2_teachers AS A INNER JOIN 
                        erp2_seniorities AS B ON A.se_id=B.seniority_id WHERE A.t_id={$t_id};";

        // 查询教师所教课程
        $teacher_cur_sql = "SELECT B.cur_id, B.cur_name FROM erp2_cur_teacher_relations 
                        AS A INNER JOIN erp2_curriculums AS B ON A.cur_id=B.cur_id 
                        WHERE A.t_id={$t_id};";

        // 查询教师所带学生
        $teacher_stu_sql = "SELECT C.stu_id, C.truename as stu_name FROM (erp2_classes_teachers_realations AS A INNER JOIN 
erp2_class_student_relations AS B ON A.cls_id=B.class_id) INNER JOIN erp2_students AS C
ON B.stu_id=C.stu_id WHERE A.t_id={$t_id};";

        // 查询教师所带班级满勤率
        $full_rate_sql = "SELECT B.class_name, count(C.stu_id)/B.class_count AS fullrat FROM (erp2_classes_teachers_realations AS A INNER JOIN erp2_classes AS B ON A.cls_id=B.class_id)
	INNER JOIN erp2_class_student_relations AS C ON B.class_id=C.class_id WHERE t_id={$t_id} GROUP BY C.stu_id;";

        $teacher = Db::query($teacher_sql);
        $teacher_cur = Db::query($teacher_cur_sql);
        $teacher_stu = Db::query($teacher_stu_sql);
        $full_rate = Db::query($full_rate_sql);
        $data = [
            'teacher' => $teacher,
            'curriculums' => $teacher_cur,
            'students' => $teacher_stu,
            'classes' => $full_rate
        ];
        $this->return_data(1, '', $data);
    }

    public function add(){

        $data = [
            't_name' => input('post.name'),
            'avator' => input('post.avator'),
            'sex' => input('post.sex',1),
            'se_id' => input('post.se_id'),
            'cellphone' => input('post.cellphone'),
            'birthday' => input('post.birthday'),
            'entry_time' => input('post.entry_day'),
            'resume' => input('post.resume'),
            'identity_card' => input('post.id_card'),
        ];
        $validate = new \app\index\validate\Teacher();
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }

        try{
            $teacher = new TeacherModel;
            $teacher->data($data);
            $teacher->allowField(true)->save();
            $t_id = $teacher->t_id;
            $cur_list = input('cur_list', null);
            foreach ($cur_list as $k=>$v)
            {
                $data = ['cur_id'=>$v, 't_id'=>$t_id];
                db('cur_teacher_relations')->data($data)->insert();
            }
            $this->return_data(1,0,'教师新增成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    /*
     * 教师离职或者复职。 1离职, 2复职。
     */
    public function jobStatus()
    {
        $t_id = input('post.id');
        $status = input('post.status', null);
        if(empty($status) || empty($t_id))
        {
            $this->return_data(0, '10000', '缺少必填参数');
        }
        try
        {
            $res = TeacherModel::where('t_id', '=', $t_id)->update(['status'=>$status]);
            if($res)
            {
                $this->return_data(1, '', '操作成功');
            }
            else
            {
                $this->return_data(0, '', '操作失败');
            }
        }catch (Exception $e)
        {
                $this->return_data(0, '50000', '服务器错误');
        }

    }

    /**
     * 删除教师课程
     */
    public function lessonDelete()
    {
        if(!$this->request->isPost())
        {
            $this->return_data(0, '10002', '请用POST方法提交');
        }
        $t_id = input('t_id'); // 教师ID
        $cur_id = input('cur_id'); //课程ID
        try
        {
            $res = db('cur_teacher_relations')->where(['t_id'=>$t_id, 'cur_id'=>$cur_id])->delete();
            $this->return_data(1, '', '删除成功', '');
        }catch (Exception $e)
        {
            $this->return_data(0, '20003', '删除失败');
        }
    }

    /*
     * 添加教师课程
     */
    public function lessonAdd()
    {
        if(!$this->request->isPost())
        {
            $this->return_data(0, '10002', '请用POST方法提交');
        }
        $t_id = input('t_id'); // 教师ID
        $cur_id = input('cur_id'); //课程ID
        try
        {
            $data = ['cur_id'=>$cur_id, 't_id'=>$t_id];
            db('cur_teacher_relations')->insert($data);
            $this->return_data(1, '', '添加课程成功', '');
        }catch (Exception $e)
        {
            $this->return_data(0, '20001', '添加课程失败');
        }
    }

    /*
     * 教师模板下载
     */
    public function template()
    {
        $org_id = input('orgid');
        $xlsName  = "教师";
        $xlsCell  = array(
            array('t_name', '教师名称'),
            array('se', '教师资历'),
            array('sex','教师性别'),
            array('identity_card', '身份证'),
            array('cellphone','电话号码'),
            array('birthday', '生日'),
            array('entry_time', '入职时间'),
            array('resume', '简历')
        );

        $xlsData = [
            [
                't_name'=>'林老师',
                'se' => '高级',
                'sex' => '男',
                'identity_card' => '43523664139694xx',
                'cellphone' => '13832832888',
                'birthday'  => '1999-11-12',
                'entry_time' => '2019-7-15',
                'resume'  =>  '十分靓仔'
            ]
        ];
//        $xlsData = db('teachers')->where('org_id', '=', $org_id)
//            ->field('t_id, t_name, sex, se_id, identity_card,
//            cellphone, birthday, entry_time, resume
//            ')->select();
//        foreach ($xlsData as $k => &$v)
//        {
//            if($v['se_id'] == 1)
//            {
//                $v['sex'] = '男';
//            }
//            elseif ($v['se_id'] == 2)
//            {
//                $v['sex'] = '女';
//            }
//            $seniorit = db('seniorities')->where('seniority_id', '=', $v['se_id'])
//                ->field('seniority_name')->find();
//            $v['se'] = $seniorit['seniority_name'];
//            $v['birthday'] = date('Y-m-d', $v['birthday']);
//            $v['entry_time'] = date('Y-m-d H:i:s', $v['entry_time']);
//        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);;
    }


    /*
     * 教师课表
     */
    public function LessonTable()
    {
        $tid = input('t_id', '');
    }

    // 教师导出
    public function export(){
        $org_id = input('orgid');
        $xlsName  = "教师";
        $xlsCell  = array(
            array('t_id','教师ID'),
            array('t_name', '教师名称'),
            array('se', '教师资历'),
            array('sex','教师性别'),
            array('identity_card', '身份证'),
            array('cellphone','电话号码'),
            array('birthday', '生日'),
            array('entry_time', '录入时间'),
            array('resume', '简历')
        );

        $xlsData = db('teachers')->where('org_id', '=', $org_id)
            ->field('t_id, t_name, sex, se_id, identity_card,
            cellphone, birthday, entry_time, resume
            ')->select();
        foreach ($xlsData as $k => &$v)
        {
            if($v['se_id'] == 1)
            {
                $v['sex'] = '男';
            }
            elseif ($v['se_id'] == 2)
            {
                $v['sex'] = '女';
            }
            $seniorit = db('seniorities')->where('seniority_id', '=', $v['se_id'])
                ->field('seniority_name')->find();
            $v['se'] = $seniorit['seniority_name'];
            $v['birthday'] = date('Y-m-d', $v['birthday']);
            $v['entry_time'] = date('Y-m-d H:i:s', $v['entry_time']);
        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);;
    }

    /*
     * 导入EXCEL
     */
    protected function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称

        $fileName =$xlsTitle . date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');

        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }

        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}


class TeacherService
{
    /** 获取教师所带学生列表
     * @param $t_id 教师ID
     */
    public static function getStudent($t_id)
    {
        //
    }

    /** 删除教师课程
     * @param $t_id
     */

}


//class Teacher extends BaseController
//{
//    /**
//     * 我的查询，用于搜索
//     */
//    protected function _where($model){
//        if(!$model){
//            return '';
//        }
//        $teacher_name = input('get.teacher_name');
//        $status = input('get.status/d');
//        $se_id = input('get.se_id/d');
//        $status?$model->where('status',$status):'';
//        $se_id?$model->where('se_id',$se_id):'';
//        $teacher_name?$model->whereLike('t_name','%'.$teacher_name.'%'):'';
//
//        return $model;
//    }
//    /**
//     * 教师列表
//     */
//    public function get(){
//       $model = \app\index\model\Teacher
//            ::field('t_id as id,t_name as name,sex,cellphone,birthday,entry_time,status,se_id')
//            ->order('create_time desc');
//       $res = $this->_where($model)->paginate(20);
//
//       $this->return_data(1,0,'',$res);
//    }
//    /**
//     * 新增教师
//     */

//
//
//    /**
//     * 编辑教师
//     */

//
//    /**
//     * 设置某些字段，如离职
//     */
//    public function set_field(){
//        $id = input('id/d');
//        $field = input('field');
//        $action = input('action');
//        $data = [
//            't_id'=>$id,
//            'field'=>$field,
//            'action'=>$action
//        ];
//        $validate = new \app\index\validate\Teacher();
//        if(!$validate->scene('field')->check($data)){
//            //为了可以得到错误码
//            $error = explode('|',$validate->getError());
//            $this->return_data(0,$error[1],$error[0]);
//        }
//        $sinfo = '';
//        try{
//            switch ($field){
//                case 1://离职
//                    $field = 'status';
//                    $sinfo = '离职成功';
//                    break;
//            }
//            \app\index\model\Teacher::where('t_id',$id)->update([$field=>$action]);
//            $this->return_data(1,0,$sinfo);
//        }catch (\Exception $e){
//            $this->return_data(0,50000,$e->getMessage());
//        }
//
//
//    }
//}