<?php
/**
 * 教师
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */

namespace app\index\controller;
use app\index\model\Teacher as TeacherModel;
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
        $t_id = input('post.t_id');
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
            $tmp = db('teach_schedules')->field('t_id')->where('t_id' ,'=', $t_id)->find();
            if(!empty($tmp))
            {
                $this->return_data(0,'20003', '已排课，删除失败');
            }
            db('teachers')->where('t_id', '=', $t_id)->delete();
            $this->return_data(1, '','删除教师成功',true);

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
        $org_id = input('org_id', null);
        $t_id = input('t_id', null);
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
            't_name' => input('post.t_name'),
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
        $t_id = input('post.t_id', '');
        $status = input('post.status', '');
        if(empty($status) || empty($t_id))
        {
            $this->return_data(0, '10000', '缺少必填参数');
        }
        try
        {
            // 排课后无法离职
            if($status == 2)
            {
                $tmp = db('teach_schedules')->field('t_id')->where('t_id' ,'=', $t_id)->find();
                if(!empty($tmp))
                {
                    $this->return_data(0,'20003', '已排课，离职失败');
                }
            }
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
            db('cur_teacher_relations')->where(['t_id'=>$t_id, 'cur_id'=>$cur_id])->delete();
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
        $org_id = input('org_id');
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
                'resume'  =>  '十年经验'
            ]
        ];
        $this->exportExcel($xlsName,$xlsCell,$xlsData);;
    }




    // 教师导出
    public function export(){
        $org_id = input('org_id');
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
            if($v['sex'] == 1)
            {
                $v['sex'] = '男';
            }
            elseif ($v['sex'] == 2)
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

    /**
     * 教师薪酬设置
     */
    public function salary()
    {
        $t_id = input('t_id', null);
        if (!isset($t_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        if ($this->request->isGet())
        {
            $data = [];
            $this->return_data(1, '', '', $data);
        }
        elseif($this->request->isPost())
        {
            $data = [];
            $this->return_data(1, '', '', $data);
        }
        else
        {
            $this->return_data(0, '10001', '请求非法');
        }
    }

    /**
     * 教师调度
     */
    public function dispatch()
    {
        $t_id = input('t_id', null);
        $org_id = input('org_id', null);
        if (!isset($t_id) || !isset($org_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        $tmp = db('teach_schedules')->field('t_id')->where('t_id' ,'=', $t_id)->find();
        if(!empty($tmp))
        {
            $this->return_data(0,'20003', '已排课，无法调度');
        }
        Db::startTrans();
        try {
            Db::table('erp2_teachers')->where('t_id', '=', $t_id)->update(['org_id'=>$org_id]);
            Db::commit();
            $this->return_data(1, '', '调度成功', true);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->return_data(0, '20002', '调度失败', false);
        }

    }

    /**
     * 班级换老师
     */
    public function changeTeacher()
    {
        //erp2_classes_teachers_realations
        $cls_id = input('cls_id', '');  //　班级id
        $t_id = input('t_id', ''); // 当前教师id
        $new_t_id = input('new_t_id', '');// 新教师id
        if(empty($t_id) || empty($new_t_id) || empty($cls_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        try
        {
            $where[] = ['t_id', '=', $t_id];
            $where[] = ['cls_id', '=', $t_id];
            $data = ['t_id' => $new_t_id];
            db('classes_teachers_realations')->where($where)->update($data);
            $this->return_data(1,'', '更换教师成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '20002', '更换教师失败', false);
        }
    }


    /*
    * 教师课表
    */
    public function schedule()
    {
        $allCode = 1;  // 全部
        $curYearCode = 2; // 本年
        $curMonthCode = 3;  // 本月
        $tid = input('t_id', null);
        $startTime = input('startTime', null);
        $endTime = input('endTime', null);
        $type = input('type', 1);  // 默认是全部
        $courseId = input('courseId', null); // 通过课程ID筛选
        $page = input('page', 1);
        $pageSize = input('size', 10);
        if (!isset($tid))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        // 查询课表数据
        $sql = "SELECT A.sc_id, A.t_id,A.cur_time, D.cur_name, B.stu_id, B.truename AS stu_name, C.room_id, C.room_name, A.status
 FROM (`erp2_teach_schedules` AS A  INNER JOIN `erp2_students` AS B ON A.stu_id=B.stu_id) 
 INNER JOIN `erp2_classrooms` AS C ON A.room_id=C.room_id 
 INNER JOIN `erp2_curriculums` AS D ON A.cur_id=D.cur_id WHERE A.t_id={$tid}";

        // 查询时间范围
        if (isset($startTime) and isset($endTime))
        {
            $rangeTime = "AND cur_name BETWEEN {$startTime} AND {$endTime}";
            $sql .= $rangeTime;
        }else
        {
            if($type==$curYearCode) //查询本年数据
            {
                $curYear = "AND DATE_FORMAT(A.cur_time,'%Y') = DATE_FORMAT(SYSDATE(),'%Y')";
                $sql .= $curYear;
            }
            elseif ($type==$curMonthCode){ // 查询本月数据
                $curMonth = "AND DATE_FORMAT( A.cur_time, '%Y%m' ) = DATE_FORMAT( CURDATE( ) , '%Y%m' )";
                $sql .= $curMonth;
            }
        }
        if(isset($courseId))
        {
            $selectCourse = "AND A.cur_id={$courseId}";
            $sql .= $selectCourse;
        }
        $startNum = ($page - 1) * $pageSize;
        $limit = "LIMIT {$startNum}, $pageSize";
        $sql .= $limit;
        $data = Db::query($sql);
        $this->return_data(1, '', '', $data);
    }

    /**
     * 删除排课记录
     */
    public function delSchedule()
    {
        $sc_id = input('sc_id', null);
        if(!isset($sc_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)->delete();
            $this->return_data(1, '','删除排课记录成功',true);
        }catch (Exception $e)
        {
            $this->return_data(0, '', '删除排课记录失败', false);
        }
    }

    /**
     * 调课
     */
    public function changeSchedule()
    {
        $sc_id = input('sc_id/d', '');
        $cur_id = input('cur_id', '');
        if (empty($sc_id) || empty($cur_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        $temp = [
            't_id'  => input('t_id/d', ''),
            'cur_time' => input('cur_time/d', ''),
            'room_id' => input('room_id/d', ''),
            'remark'  => input('remark', ''),
        ];
        $data  = [];
        foreach ($temp as $k=>$v)
        {
            if (!empty($v))
            {
                $data[$k] = $v;
            }
        }
        // 更改了上课时间需要判断是否合法
        if (isset($data['cur_time']))
        {
            if($data['cur_time']  < time())
            {
                $this->return_data(0, '10001', '排课时间有误');
            }
            // 课程时长分钟数
            $cur_min = db('curriculums')->where('cur_id', '=', $cur_id)->field('ctime')->find();

            $end_time = $data['cur_time'] + $cur_min * 60;
            $start_time = $data['cur_time'];
            $sql = "SELECT * FROM erp2_teach_schedules WHERE cur_time BETWEEN {$start_time} AND {$end_time};";
            $res = Db::query($sql);
            if(!empty($res))
            {
                $this->return_data(0, '20002', '调课失败');
            }

        }
        try{
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update($data);
            $this->return_data(1, '', '', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '20002', '调课失败');
        }
    }

    /*
     * 课程顺延
     */
    public function schedulePostpone()
    {
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
        {
            $this->return_data(0, '', '缺少参数', false);
        }
        // 查询上课时间，　和课程时长
        $sql = "SELECT A.cur_time, B.ctime FROM erp2_teach_schedules
                AS A INNER JOIN erp2_curriculums B ON A.cur_id=B.cur_id WHERE A.sc_id={$sc_id}";
        $data = Db::query($sql);
        $cur_time = $data['cur_time'];
        $c_time = $data['ctime'];
        $day_timestamp =  60 * 60 * 24; // 顺延一天
        $start_time = $cur_time + $day_timestamp; // 顺延后的上课时间
        $end_time = $cur_time + $day_timestamp + $c_time * 60; // 顺延后的课程结束时间
        $sql = "SELECT * FROM erp2_teach_schedules WHERE cur_time BETWEEN {$start_time} AND {$end_time};";
        $res = Db::query($sql);
        if (!empty($res))
        {
            $this->return_data(0, '20002', '顺延失败, 课程冲突', false);
        }
        try{
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update(['cur_time' => $cur_time]);
            $this->return_data(1, '', '顺延成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '', '服务器错误', false);
        }
    }

    /**
     * 请假
     */
    public function updatePending()
    {
        /** erp2_teach_schedule表状态:
         *1 是正常
         *2 是消课
         *3 是请假
         *4 是旷课
         */
        $status = 3;
        $sc_id = input('sc_id', '');
        $level = input('level', 1); // 1事假, 2病假, 老师请假
        if(empty($sc_id) || $level)
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
            ->update(['level'=> $level, 'status'=>$status]);
            $this->return_data(1, '', '请假成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '', '服务器错误，　请假失败', true);
        }
    }

    /**
     * 取消请假状态
     */
    public function cancelPending()
    {
        $status = 1;  //　设为正常状态
        $sc_id = input('sc_id', '');
        $level = 0; // 1事假, 2病假, 3老师请假, 0 非请假
        if(empty($sc_id) || $level)
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['level'=> $level, 'status'=>$status]);
            $this->return_data(1, '', '取消请假成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '', '服务器错误，　请假失败', true);
        }
    }

    /**
     * 旷课
     */
    public function updateTruancy()
    {
        $status = 4;  //　设为旷课状态
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['status'=>$status]);
            $this->return_data(1, '', '取消请假成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '', '服务器错误，　请假失败', true);
        }
    }

    /*
     * 取消旷课
     */
    public function cancelTruancy()
    {
        $status = 1;  //　设为正常状态
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['status'=>$status]);
            $this->return_data(1, '', '取消旷课成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '', '服务器错误，　请假失败', true);
        }
    }

    /*
     * 还原默认状态
     */
    public function scheduleRecover()
    {
        $status = 1;  //　设为正常状态
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['status'=>$status]);
            $this->return_data(1, '', '还原成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, '', '服务器错误，　请假失败', true);
        }
    }
}

