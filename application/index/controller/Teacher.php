<?php
/**
 * 教师
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */

namespace app\index\controller;
use app\index\model\Teacher as TeacherModel;
use app\index\validate\Teachers as TeachersValidate;
use think\Controller;
use think\Db;
use think\db\Where;
use think\Exception;
use think\Log;

trait Response
{
    public function return_data($status=1,$error_no=0,$info='',$data=''){
        $status = empty($status)?false:true;
        if($status){
            $key = 'sinfo';
        }else{
            $key = 'error_msg';
        }
        echo json_encode(['status'=>$status,'error_code'=>$error_no,$key =>$info,'data'=>$data]);
        exit();
    }
}


/*
 * Teacher-related Functional Controller
 */
class Teacher extends BaseController
{
    /*
     * Returning the details of teachers
     * can be screened by teachers'qualifications and on-the-job status of teachers' names.
     */
//    use Response;
    public function index()
    {
        $org_id = input('orgid', '');
        $t_name = input('t_name/s', null); // 教师名称
        $se_id = input('se_id/s', null); // 资历ID
        $status = input('status/d', null);  // 离职状态
        $where = array();
        if(empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
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
        $where[] = ['is_del', '=', 0];
        $where[] = ['org_id', '=', $org_id];
        $teacher = TeacherModel::where($where)->field('t_id as id,t_name as name, avator,
                sex,cellphone,birthday,entry_time,status, se_id, resume')->order('create_time DESC')->paginate(20);
        $this->return_data(1, '','', $teacher);
    }

    /*
     * Modifying Teacher Information Method
     */
    public function edit(){
        $t_id = input('post.t_id', '');
        $org_id = input('post.orgid', '');
        if (empty($t_id) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少orgid或者t_id');
        }
        $data = [
            't_id'=>$t_id,
            'org_id' => $org_id,
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
        if (!$validate->check($data)) {
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        Db::startTrans();
        try{
            $salary = input('salary');
            $basic_wages = $salary['basic_wages'];
            $wages_type = $salary['wages_type'];
            if(!is_numeric($basic_wages) || $basic_wages < 0)
            {
                $this->return_data(0, '10000', '基本工资参数有误');
            }
            if($wages_type != 1 and $wages_type!=0)
            {
                $this->return_data(0, '10000', '工资类型有误');
            }
            $s_id = $salary['s_id'];
            $courses_list = $salary['courses'];
            //TeacherModel::update($data,['t_id'=>$data['t_id']]);
            Db::name('teachers')->where('t_id', '=', $t_id)->update($data);
            Db::name('teacher_salary')->where('s_id', '=', $s_id)->update(
                ['basic_wages'=>$basic_wages],
                ['wages_type'=>$wages_type]
            );
            Db::name('teacher_salary')->where('s_id', '=', $s_id)->update(['basic_wages'=>$basic_wages, 'wages_type'=>$wages_type]);
            foreach ($courses_list as $k=>$v)
            {
                if (!isset($v['cur_id']) || !isset($v['p_id']) || !isset($v['p_num']))
                {
                    Db::rollback();
                    $this->return_data(0, '10000', '薪酬设置参数错误');
                }
            }
            Db::commit();
            $this->return_data(1,0,'编辑教师成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    /*
     * Delete teacher by teacher's id.
     */
    public function del()
    {
        $t_id = input('t_id/d', '');
        $org_id = input('orgid/d', '');
        if(empty($t_id) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少t_id或orgid');
        }
        try {
            $tmp = db('teach_schedules')->field('t_id')->where('t_id' ,'=', $t_id)->select();
            if(count($tmp) > 0)
            {
                $this->return_data(0,'20003', '已排课，删除失败');
            }
            $where[] = ['t_id', '=', $t_id];
            $where[] = ['org_id', '=', $org_id];
            $where[] = ['is_del', '=', 0];
            db('teachers')->where($where)->update(['is_del'=>1]);
            $this->return_data(1, '','删除教师成功',true);

        }catch (Exception $e)
        {
            $this->return_data(0, '20003', '删除教师失败');
        }
    }

    /**
     * Show teacher's detail information.
     */
    public function detail()
    {
        $org_id = input('orgid', null);
        $t_id = input('t_id', null);
        if (!isset($org_id) || !isset($t_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }

        // 教师详细信息
        $teacher_details = db('teachers')->alias('A')
                            ->join('erp2_seniorities B', 'A.se_id=B.seniority_id')
                             ->field(' B.seniority_id as se_id, B.seniority_name as se_name,
                                            A.avator AS avator, A.identity_card as id_card, A.t_id as tid, 
                                            A.t_name as name, A.sex, A.cellphone, A.birthday, A.entry_time, 
                                            A.status,  A.resume')
                            ->where('A.t_id', '=', $t_id)
                            ->find();
        if (empty($teacher_details))
        {
            $this->return_data(0, '20000', '教师不存在', '');
        }
        // 查询教师班级ID列表
        $teacher_classes = db('classes_teachers_realations')->field('cls_id')->where('t_id', '=', $t_id)->select();
//        $this->return_data('１', '', '', $teacher_classes);
//        exit();
        $teacher_classes_data = array();
        foreach ($teacher_classes as $k=>$v)
        {
            // 补充班级名称和班级满班率
            $cls_id = $v['cls_id'];
            $sql = "SELECT A.class_name AS cls_name, count(B.class_id)/A.class_count AS cls_rate FROM erp2_classes AS A
                    LEFT JOIN erp2_class_student_relations AS B ON A.class_id=B.class_id 
                    WHERE A.class_id={$cls_id} GROUP BY A.class_name LIMIT 1";
            $temp = Db::query($sql);
            $temp['cls_id'] = $v['cls_id'];

            $teacher_classes_data[] = [
                'cls_id' => $cls_id,
                'cls_name' => $temp[0]['cls_name'],
                'cls_rate' => $temp[0]['cls_rate']
            ];
            unset($temp, $cls_id, $sql);
        }

        // 查找教师所带的学生
        $teacher_students = array();
        foreach ($teacher_classes as $k=>$v)
        {
            $cls_id = $v['cls_id'];
            $sql = "SELECT  B.stu_id, B.truename AS stu_name FROM erp2_class_student_relations AS A 
                    INNER JOIN erp2_students AS B ON A.stu_id=B.stu_id WHERE A.class_id={$cls_id}";
            $temp = Db::query($sql);
            $teacher_students = array_merge($teacher_students, $temp);
            $teacher_students = array_unique($teacher_students);
            unset($temp);
        }

        // 查询教师薪酬基本信息
        $teacher_salary = db('teacher_salary')->field('s_id, basic_wages, wages_type')
                        ->where('t_id', '=', $t_id)->find();


        $teacher_course_list = db('cur_teacher_relations')->
        field('cur_id')->where('t_id', '=', $t_id)->select();

        foreach ($teacher_course_list as $k=>$v)
        {

            $cur_id = $v['cur_id'];
            $where = [
                ['s_id', '=', $teacher_salary['s_id']],
                ['cur_id', '=', $cur_id]
            ];
            $salary = db('teacher_salary_cur')
                ->field('cur_id, p_id, p_num')
                ->where($where)
                ->find();
            if (!isset($salary['cur_id']))
            {
                $p_id = 1;
                $p_num = 0;
                $p_name = '按出勤人数';
                $p_unit = '元/人';
            }
            else
            {
                $p_id = $salary['cur_id'];
                $p_num = $salary['p_num'];
                $temp = db('pay_id_info')->field('pay_name as p_name, cpany as p_unit')
                    ->where('pay_id_info', '=', $p_id)->find();
                $p_name = $temp['p_name'];
                $p_unit = $temp['p_unit'];
            }

            $cur_name = db('curriculums')->where('cur_id', '=', $cur_id)->value('cur_name');

            // 详细课程薪酬列表
            $teacher_salary['courses'][] = [
                'p_id' => $p_id,
                'p_name' => $p_name,
                'p_num' => $p_num,
                'p_unit' => $p_unit,
                'cur_id' => $cur_id,
                'cur_name' => $cur_name
            ];
            unset($p_id, $p_name, $p_num, $p_unit, $cur_id, $cur_name);
        }
        $data = [
            'teacher' => $teacher_details,
            'salary' => $teacher_salary,
            'students' => $teacher_students,
            'classes' => $teacher_classes_data,
        ];
        $this->return_data(1, '', '请求成功', $data);
    }

    /*
     * add teacher information.
     */

    public function add(){

        if (!$this->request->isPost())
        {
            $this->return_data(0, '40000', '非法请求');
        }
        $data = [
            't_name' => input('post.t_name/s', ''),
            'avator' => input('post.avator/s', ''),
            'sex' => input('post.sex/d',1),
            'se_id' => input('post.se_id/d', ''),
            'cellphone' => input('post.cellphone/s', ''),
            'birthday' => input('post.birthday/d', ''),
            'entry_time' => input('post.entrytime/d', ''),
            'resume' => input('post.resume/s', ''),
            'org_id' => input('orgid/d', ''),
            'identity_card' => input('post.id_card/s', ''),
        ];
        if($data['entry_time'] < 0 || $data['birthday'] < 0)
        {
            $this->return_data(0, '10000', '时间戳参数错误');
        }
        $validate = new TeachersValidate;
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|', $validate->getError());
            $this->return_data(0, $error[1], $error[0]);
        }
        try{
            $cur_str = input('cur_list/s', '');
            if (empty($cur_str))
            {
                $this->return_data('0', '10000', '缺少cur_list参数', false);
            }
            $cur_list = explode(',', $cur_str);
            $teacher = new TeacherModel;
            $teacher->data($data);
            $teacher->save();
            $t_id = $teacher->t_id;
            for($i = 0; $i < count($cur_list); $i++)
            {
                $data = ['cur_id'=>intval($cur_list[$i]), 't_id'=>$t_id];
                db('cur_teacher_relations')->data($data)->insert();
            }
            // 添加教师薪酬ID
            Db::name('teacher_salary')->insert(['t_id'=>$t_id]);
            $this->return_data(1,0,'教师新增成功', true);
        }catch (\Exception $e){

            $this->return_data(0,50000, '服务器错误'. $e->getMessage());
        }
    }


    /*
     * 教师离职或者复职。 1离职, 2复职。
     */
    public function jobStatus()
    {
        $org_id = input('org_id', null);
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
    public function lessonDel()
    {
//        $org_id = input('orgid');
        if(!$this->request->isPost())
        {
            $this->return_data(0, '10002', '请用POST方法提交');
        }
        $t_id = input('t_id'); // 教师ID
        $cur_id = input('cur_id'); //课程ID
        if (empty($t_id) || empty($cur_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
        try
        {
            $cur_count = db('cur_teacher_relations')->where(['t_id'=>$t_id])->count();
            if ($cur_count > 1)
            {
                db('cur_teacher_relations')->where(['t_id'=>$t_id, 'cur_id'=>$cur_id])->delete();
                $this->return_data(1, '', '删除成功', '');
            }
            else
            {
                $this->return_data(0, '', '删除失败, 至少保留一门课程。', '');
            }
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
//        $org_id = input('orgid', '');
        $t_id = input('t_id', ''); // 教师ID
        $cur_id = input('cur_id', ''); //课程ID
        if (empty($t_id) || empty($cur_id))
        {
            $this->return_data(0, '10000', '缺少参数', false);
        }
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
     * 返回所有的课程列表，标记老师当前已选的课程。
     */
    public function teacher_courses()
    {
        $t_id = input('t_id', '');
        $org_id = input('orgid', '');
        if (empty($t_id) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少t_id或org_id', false);
        }
        $data = db('subjects')->field('sid, sname')->select();
        foreach ($data as $k=>$v) {
            $temp = db('curriculums')->
            field('cur_id, cur_name')->
            where(['orgid'=>$org_id, 'subject'=>$v['sid']])->select();
            for($i=0; $i<count($temp); $i++)
            {
                $where = [
                    ['t_id', '=', $t_id],
                    ['cur_id', '=', $temp[$i]['cur_id']],
                    ['is_del', '=', 0]
                ];
                $res = db('cur_teacher_relations')->where($where)->count();
                if ($res > 0)
                {
                    $temp[$i]['status'] = 1;
                }
                else
                {
                    $temp[$i]['status'] = 0;
                }
            }
            $data[$k]['courses']= $temp;
            unset($temp);
        }
        $this->return_data(1, '', '请求成功', $data);
    }


    /*
    * 教师课表
    */
    public function schedule()
    {
//        $this->return_data(1,'', '', '');
        $allCode = 1;  // 全部
        $org_id = input('orgid/d', '');
        $curYearCode = 2; // 本年
        $curMonthCode = 3;  // 本月
        $tid = input('t_id/d', '');
        $startTime = input('startTime/d', '');
        $endTime = input('endTime/d', '');
        $type = input('type/d', 1);  // 默认是全部
        $courseId = input('courseId/d', ''); // 通过课程ID筛选
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        if (empty($tid) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }
        $data = array();
        $tables = Db::name('teach_schedules')->field('sc_id, stu_id, room_id, cur_time, cur_id, status')
                    ->where(['org_id'=>$org_id, 't_id'=>$tid]);
        if(!empty($courseId))
        {
            $tables->where('cur_id', '=', $courseId);
        }
        if (!empty($startTime) and !empty($endTime))
        {
//            $tables->where('cur_time','between',[$startTime, $endTime]);
            $tables->whereBetweenTime('cur_time', $startTime, $endTime);
        }else
        {
            if($type==$curYearCode) //查询本年数据
            {
                $tables->whereTime('cur_time', 'y');
            }
            elseif ($type==$curMonthCode){ // 查询本月数据
                $tables->whereTime('cur_time', 'm');
            }
        }
        $data = $tables->paginate($limit);
//        $this->return_data(1,'', '', $tables->fetchSql());
        $response = array();
        foreach ($data as $k=>$v)
        {
            $status = $v['status'];
            $sc_id = $v['sc_id'];
            $cur_id = $v['cur_id'];
            $cur_time = $v['cur_time'];
            $stu_id = $v['stu_id'];
            $room_id = $v['room_id'];
            $stu_name = db('students')->where('stu_id', '=', $stu_id)->value('truename');
            $room_name = db('classrooms')->where('room_id', '=', $room_id)->value('room_name');
            $temp = db('curriculums')->where('cur_id', '=', $cur_id)->field('cur_name, tmethods as cur_type')->find();

            $response[] = [
                'sc_id' => $sc_id,
                'cur_id' => $cur_id,
                'cur_name' => $temp['cur_name'],
                'cur_type'  => $temp['cur_type'],
                'cur_time'  => $cur_time,
                'stu_id'  => $stu_id,
                'stu_name' => $stu_name,
                'room_id'   => $room_id,
                'room_name' => $room_name,
                'status'    => $status
            ];
        }

        $this->return_data(1, '', '', $response);
    }

    /**
     * 取消消课
     */
    public function close_cancel_course()
    {
        $sc_id = input('sc_id/d', '');
        $password = input('password', '');
        $uid = input('uid', '');
        if(empty($sc_id) || empty($password) || empty($uid))
        {
            $this->return_data(1, '10000', '缺少参数');
        }
        try
        {
            $req_pwd_md5 = md5_return($password);
            $db_pwd_md5 = db('users')->where('uid', '=', $uid)->value('password');
            if (empty($db_pwd_md5) || $req_pwd_md5!=$db_pwd_md5)
            {
                $this->return_data('0', '20002', '取消消课失败，密码错误', false);
            }
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update(['status' => 1]);
            $this->return_data(0,'' , '取消消课成功', true);
        }catch (Exception $e)
        {
            $this->return_data(1, '20003', '系统出错,取消消课失败', false);
        }
    }

    /**
     * 删除排课记录
     */
    public function schedule_del()
    {
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
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
        $cur_id = input('cur_id/d', '');
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
            $cur_min = db('curriculums')->where('cur_id', '=', $cur_id)->value('ctime');

            $end_time = $data['cur_time'] + $cur_min * 60;
            $start_time = $data['cur_time'];
            $res = db('teach_schedules')->whereTime('cur_time', [$start_time, $end_time])->select();

            if(!empty($res))
            {
                $this->return_data(0, '20002', '调课失败');
            }

        }
        try{
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update($data);
            $this->return_data(1, '', '调课成功', true);
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
        $sc_id = input('sc_id/d', '');
        if(empty($sc_id))
        {
            $this->return_data(0, '', '缺少参数', false);
        }
        try
        {
            // 查询上课时间，　和课程时长
            $data = db('teach_schedules')->alias('A')
                ->join('erp2_curriculums B', 'A.cur_id=B.cur_id')
                ->field('A.cur_time, B.ctime, A.room_id')->where('A.sc_id', '=', $sc_id)->find();
            $cur_time = $data['cur_time'];
            $c_time = $data['ctime'];
            $room_id = $data['room_id'];
            $day_timestamp =  60 * 60 * 24; // 顺延一天
            $start_time = $cur_time + $day_timestamp; // 顺延后的上课时间
            $end_time = $cur_time + $day_timestamp + $c_time * 60; // 顺延后的课程结束时间
            $where = [
                ['room_id', '=', $room_id],
                ['status', '=', 1]
            ];
            $res = db('teach_schedules')->whereTime('cur_time', [$start_time, $end_time])
                ->where($where)->find();
            if (!empty($res))
            {
                $this->return_data(0, '20002', '顺延失败, 课程冲突', false);
            }
            // 更新上课时间
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update(['cur_time' => $start_time]);
            $this->return_data(1, '', '顺延成功', true);
        }catch (Exception $e)
        {
            $this->return_data('0', '50000', '服务器错误');
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
        $level = input('level', 1); // 1事假, 2病假, 3老师请假
        if(empty($sc_id) || empty($level))
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
        if(empty($sc_id))
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
        $sc_id = input('sc_id/d', '');
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
            $this->return_data(0, '', '服务器错误，还原失败', true);
        }
    }


    /**
     * 课程列表
     */
    public function course()
    {
        $orgid = input('orgid');
        if (empty($orgid))
        {
            $this->return_data('0', '10000', '缺少参数orgid');
        }
        $data = db('subjects')->field('sid, sname')->select();
        foreach ($data as $k=>$v) {
            $temp = db('curriculums')->
                    field('cur_id, cur_name')->
                    where(['orgid'=>$orgid, 'subject'=>$v['sid']])->select();
            $data[$k]['courses']=$temp;
            unset($temp);
        }
        $this->return_data(1, '', '', $data);
    }

    /*
     * 教师下拉列表
     */
    public function selectedTeacher()
    {
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->return_data('0', '10000','缺少参数');
        }
        $data = db('teachers')->field('t_id, t_name')->where(['org_id'=>$org_id, 'is_del'=>0])->select();
        $this->return_data('1', '', '请求成功', $data);
    }

    /*
     * 教室下拉列表
     */
    public function selectedRoom()
    {
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->return_data('0', '10000','缺少参数');
        }
        $data = db('classrooms')->field('room_id, room_name')->where('or_id','=', $org_id)->select();
        $this->return_data('1', '', '请求成功', $data);
    }
}

