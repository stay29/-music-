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
use think\helper\Str;
use think\facade\Log;


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
        $this->auth_get_token();
        $org_id = input('orgid', '');
        $t_name = input('t_name/s', ''); // 教师名称
        $se_id = input('se_id/d', ''); // 资历ID
        $status = input('status/d', '');  // 离职状态
        $cur_id=input('cur_id/d','');//课程id
        $limit = input('limit/d', 20);
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        $teacher = TeacherModel::where('org_id', '=', $org_id)->alias("a");
        if(!empty($t_name) || $t_name==0)
        {
            $teacher->where('t_name', 'like', '%' . $t_name . '%');
        }
        if(!empty($se_id))
        {
            $teacher->where('se_id', '=', $se_id);
        }

        if(!empty($status))
        {
            $teacher->where('status', '=', $status);
        }
        $teacher->where('a.is_del', '=', 0);
        if(!empty($cur_id)){
            $teacher->join('erp2_cur_teacher_relations b','a.t_id=b.t_id and b.is_del=0 and b.cur_id='.$cur_id);
        }
        $response = $teacher->field('a.t_id as id,a.t_name as name, a.avator,
                a.sex,a.cellphone,a.entry_time,a.status, a.se_id, a.resume')->order('create_time DESC')->paginate($limit);
        $this->returnData($response, '请求成功');
    }

    /*
     * Modifying Teacher Information Method
     */
    public function edit(){
        $this->auth_get_token();
        $t_id = input('post.t_id/d', '');
        $org_id = input('post.orgid/d', '');

        if (empty($t_id) || empty($org_id))
        {
            $this->returnError('10000', '缺少orgid或者t_id');
        }

        $data = [
            't_id'  => $t_id,
            'org_id' => $org_id,
            't_name' => input('post.t_name/s'),
            'avator' => input('post.avator', ''),
            'sex' => input('post.sex/d',1),
            'se_id' => input('post.se_id/d', ''),
            'cellphone' => input('post.cellphone/s', ''),
            'birthday' => input('post.birthday/d', 0),
            'entry_time' => input('post.entrytime/d', 0),
            'resume' => input('post.resume/s', ''),
            'identity_card' => input('post.id_card/s', ''),
            'manager' => input('post.uid/d', 1),
            'update_time' => time(),
        ];
        $validate = new TeachersValidate();
        if (!$validate->scene('edit')->check($data)) {
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->returnError($error[1],$error[0]);
        }
        Db::startTrans();
        try{

            $salary = input('post.salary');
            if (!isset($salary['basic_wages']) || !isset($salary['wages_type']) || !isset($salary['s_id']))
            {
                $this->returnError('10000', '缺少basic_wages或wages_type或s_id');
            }
            $basic_wages = floatval($salary['basic_wages']);
            $wages_type = floatval($salary['wages_type']);
            if(!is_numeric($basic_wages) || $basic_wages < 0)
            {
                $this->returnError('10000', '基本工资参数有误');
            }
            if($wages_type != 1 and $wages_type!=0)
            {
                $this->returnError('10000', '工资类型有误');
            }
            $s_id = $salary['s_id'];
            $courses_list = $salary['courses'];

            Log::write('更新教师' . $t_id . '数据:' . json_encode($data));
            $res = Db::name('teachers')->where('t_id', '=', $t_id)->update($data);
            if (!$res)
            {
                Db::rollback();
                Log::write('更新教师信息失败');
                $this->returnError( '20003', '更新失败');
            }
            // 更新教师基本工资
            $res = Db::name('teacher_salary')->where(['s_id' => $s_id])->update([
                'basic_wages' => $basic_wages,
                'wages_type' => $wages_type,
                'update_time' => time()
            ]);
            if (!$res)
            {
                Db::rollback();
                Log::write("更新教师基本薪酬失败");
                $this->returnError('20003', '更新失败');
            }
            foreach ($courses_list as $k=>$v)
            {
                if (!isset($v['cur_id']) || !isset($v['p_id']) || !isset($v['p_num']))
                {
                    Db::rollback();
                    $this->return_data(0, '10000', '薪酬设置参数错误');
                }
                $d  = [
                    'p_id' => $v['p_id'],
                    'p_num' => $v['p_num'],
                    'cur_id' => $v['cur_id'],
                    's_id' => $s_id
                ];
                $salary_id = db('teacher_salary_cur')->where(['s_id'=>$s_id, 'cur_id'=>$v['cur_id']])
                    ->value('id');
                if (empty($salary_id))
                {
                    Db::name('teacher_salary_cur')->insert($d);
                }
                else
                {
                    Db::name('teacher_salary_cur')->where('id', '=', $salary_id)->update($d);
                }
                unset($d);
            }
            Db::commit();
            $this->returnData(1,'编辑教师成功');
        }catch (\Exception $e){
            Db::rollback();
            $this->returnError(50000,$e->getMessage());
        }
    }

    /*
     * Delete teacher by teacher's id.
     */
    public function del()
    {
        $this->auth_get_token();
        $t_id = input('t_id/d', '');
        $org_id = input('orgid/d', '');
        if(empty($t_id) || empty($org_id))
        {
            $this->returnError( '10000', '缺少t_id或orgid');
        }
        Db::startTrans();
        try {
            $tmp = Db::name('teach_schedules')->field('t_id')->where('t_id' ,'=', $t_id)->select();
            Log::write("删除教师课表");
            if(count($tmp) > 0)
            {
                $this->returnError('20003', '已排课，删除失败');
            }
            Db::name('cur_teacher_relations')->where('t_id', '=', $t_id)->delete();
            Log::write("删除教师课程关联表");
            $s_id = Db::name('teacher_salary')->where('t_id', '=', $t_id)->value('s_id');
            if(!$s_id)
            {
                Log::write("删除教师课程薪酬");
                Db::name('teacher_salary_cur')->where('s_id', '=', $s_id)->delete();
            }
            Log::write('删除教师班级关联表:');
            Db::name('classes_teachers_realations')->where('t_id', '=', $t_id)->delete();
            Log::write("删除班级教师关联");
            Db::name('teacher_salary')->where('t_id')->delete();
            Log::write("删除教师薪酬");
            $where[] = ['t_id', '=', $t_id];
            $where[] = ['org_id', '=', $org_id];
            $where[] = ['is_del', '=', 0];
            Db::name('teachers')->where($where)->delete();
            Log::write("删除教师");
            Db::commit();
            $this->returnData(1,'删除教师成功');

        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError('20003', '删除教师失败');
        }
    }

    /**
     * Show teacher's detail information.
     */
    public function detail()
    {
        $this->auth_get_token();
        $org_id = input('orgid', null);
        $t_id = input('t_id', null);
        if (!isset($org_id) || !isset($t_id))
        {
            $this->returnError('10000', '缺少参数');
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
            $this->returnError('20000', '教师不存在');
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
            if (empty($salary))
            {
                $p_id = 1;
                $p_num = 0;
                $p_name = '按出勤人数';
                $p_unit = '元/人';
            }
            else
            {
                $p_id = $salary['p_id'];
                $p_num = $salary['p_num'];
                $temp = db('pay_info')->field('pay_name as p_name, cpany as p_unit')
                    ->where(['pay_id_info'=>$p_id, 'orgid'=>$org_id])->find();
                $p_name = $temp['p_name'];
                $p_unit = $temp['p_unit'];
            }

            $cur_data = db('curriculums')->where('cur_id', '=', $cur_id)
                ->field('cur_name, tmethods as cur_type')->find();

            // 详细课程薪酬列表
            $teacher_salary['courses'][] = [
                'p_id' => $p_id,
                'p_name' => $p_name,
                'p_num' => $p_num,
                'p_unit' => $p_unit,
                'cur_id' => $cur_id,
                'cur_name' => $cur_data['cur_name'],
                'cur_type'  => $cur_data['cur_type']
            ];
            unset($p_id, $p_name, $p_num, $p_unit, $cur_id, $cur_name);
        }
        $data = [
            'teacher' => $teacher_details,
            'salary' => $teacher_salary,
            'students' => $teacher_students,
            'classes' => $teacher_classes_data,
        ];
        $this->returnData($data, '请求成功');
    }

    /*
     * add teacher information.
     */

    public function add(){
        $this->auth_get_token();
        if (!$this->request->isPost())
        {
            $this->returnError('40000', '非法请求');
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
        if($data['entry_time'] < 0)
        {
            $this->returnError('10000', '时间戳参数错误');
        }
        $validate = new TeachersValidate;
        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|', $validate->getError());
            $this->returnError( $error[1], $error[0]);
        }
        try{
            $cur_str = input('cur_list/s', '');
            if (empty($cur_str))
            {
                $this->returnError('10000', '缺少cur_list参数', false);
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
            Db::name('teacher_salary')->insert(['t_id'=>$t_id, 'create_time'=>time()]);
            $this->returnData(1,'教师新增成功');
        }catch (\Exception $e){

            $this->returnError(50000, '服务器错误');
        }
    }


    /*
     * 教师离职或者复职。 1离职, 2复职。
     */
    public function jobStatus()
    {
        $this->auth_get_token();
        $org_id = input('org_id', null);
        $t_id = input('post.t_id', '');
        $status = input('post.status', '');
        if(empty($status) || empty($t_id))
        {
            $this->returnError('10000', '缺少必填参数');
        }
        try
        {
            // 排课后无法离职
            if($status == 2)
            {
                $tmp = db('teach_schedules')->field('t_id')->where('t_id' ,'=', $t_id)->find();
                if(!empty($tmp))
                {
                    $this->returnError('20003', '已排课，离职失败');
                }
            }
            $res = TeacherModel::where('t_id', '=', $t_id)->update(['status'=>$status]);
            if($res)
            {
                $this->returnData('', '操作成功');
            }
            else
            {
                $this->returnError(20003, '操作失败');
            }
        }catch (Exception $e)
        {
                $this->return_data('50000', '服务器错误');
        }

    }

    /**
     * 删除教师课程
     */
    public function lessonDel()
    {
        $this->auth_get_token();
//        $org_id = input('orgid');
        if(!$this->request->isPost())
        {
            $this->returnError('10002', '请用POST方法提交');
        }
        $t_id = input('t_id'); // 教师ID
        $cur_id = input('cur_id'); //课程ID
        if (empty($t_id) || empty($cur_id))
        {
            $this->returnError( '10000', '缺少参数', false);
        }
        try
        {
            $cur_count = db('cur_teacher_relations')->where(['t_id'=>$t_id])->count();
            if ($cur_count > 1)
            {
                db('cur_teacher_relations')->where(['t_id'=>$t_id, 'cur_id'=>$cur_id])->delete();
                $this->returnData(1,  '删除成功');
            }
            else
            {
                $this->returnError('20003',  '删除失败, 至少保留一门课程。');
            }
        }catch (Exception $e)
        {
            $this->returnError('20003', '删除失败');
        }
    }

    /*
     * 添加教师课程
     */
    public function lessonAdd()
    {
        $this->auth_get_token();
        if(!$this->request->isPost())
        {
            $this->return_data(0, '10002', '请用POST方法提交');
        }
        $t_id = input('t_id', ''); // 教师ID
        $cur_id = input('cur_id', ''); //课程ID
        if (empty($t_id) || empty($cur_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try
        {
            $data = ['cur_id'=>$cur_id, 't_id'=>$t_id];
            db('cur_teacher_relations')->insert($data);
            $this->returnData(1,  '添加课程成功');
        }catch (Exception $e)
        {
            $this->returnError('20001', '添加课程失败');
        }
    }


    /**
     * 教师调度
     */
    public function dispatch()
    {
        $this->auth_get_token();
        $t_id = input('t_id', null);
        $org_id = input('org_id', null);
        if (!isset($t_id) || !isset($org_id))
        {
            $this->returnError( '10000', '缺少参数');
        }
        $tmp = db('teach_schedules')->field('t_id')->where('t_id' ,'=', $t_id)->find();
        if(!empty($tmp))
        {
            $this->returnError('20003', '已排课，无法调度');
        }
        Db::startTrans();
        try {
            Db::table('erp2_teachers')->where('t_id', '=', $t_id)->update(['org_id'=>$org_id]);
            Db::commit();
            $this->returnData(1,  '调度成功');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->returnError('20002', '调度失败');
        }

    }

    /**
     * 班级换老师
     */
    public function changeTeacher()
    {
        $this->auth_get_token();
        //erp2_classes_teachers_realations
        $cls_id = input('cls_id', '');  //　班级id
        $t_id = input('t_id', ''); // 当前教师id
        $new_t_id = input('new_t_id', '');// 新教师id
        if(empty($t_id) || empty($new_t_id) || empty($cls_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try
        {
            $where[] = ['t_id', '=', $t_id];
            $where[] = ['cls_id', '=', $t_id];
            $data = ['t_id' => $new_t_id];
            db('classes_teachers_realations')->where($where)->update($data);
            $this->returnData(1,'更换教师成功');
        }catch (Exception $e)
        {
            $this->returnError('20002', '更换教师失败');
        }
    }

    /*
     * 返回所有的课程列表，标记老师当前已选的课程。
     */
    public function teacher_courses()
    {
        $this->auth_get_token();
        $t_id = input('t_id', '');
        $org_id = input('orgid', '');
        if (empty($t_id) || empty($org_id))
        {
            $this->returnError( '10000', '缺少t_id或org_id');
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
        $this->returnData($data, '请求成功');
    }


    /*
    * 教师课表
    */
    public function schedule()
    {
        $this->auth_get_token();
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
            $this->returnError('10000', '缺少参数');
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
        $data = $tables->order('create_time DESC')->paginate($limit);
        $response = [
            'total' => $data->total(),
            'per_page' => $limit,
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'data' => array(),
        ];
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

            $response['data'][] = [
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

        $this->returnData($response, '请求成功');
    }

    /**
     * 取消消课
     */
    public function close_cancel_course()
    {
        $this->auth_get_token();
        $sc_id = input('sc_id/d', '');
        $password = input('password', '');
        $uid = input('uid', '');
        if(empty($sc_id) || empty($password) || empty($uid))
        {
            $this->returnError('10000', '缺少参数');
        }
        try
        {
            $req_pwd_md5 = md5_return($password);
            $db_pwd_md5 = db('users')->where('uid', '=', $uid)->value('password');
            if (empty($db_pwd_md5) || $req_pwd_md5!=$db_pwd_md5)
            {
                $this->returnError('20002', '取消消课失败，密码错误');
            }
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update(['status' => 1]);
            $this->returnData('' , '取消消课成功');
        }catch (Exception $e)
        {
            $this->returnError('20003', '系统出错,取消消课失败');
        }
    }

    /**
     * 删除排课记录
     */
    public function schedule_del()
    {
        $this->auth_get_token();
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
        {
            $this->returnError( '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)->delete();
            $this->returnData( '','删除排课记录成功',true);
        }catch (Exception $e)
        {
            $this->returnError(0,  '删除排课记录失败');
        }
    }

    /**
     * 调课
     */
    public function changeSchedule()
    {
        $this->auth_get_token();
        $sc_id = input('sc_id/d', '');
        $cur_id = input('cur_id/d', '');
        if (empty($sc_id) || empty($cur_id))
        {
            $this->returnError('10000', '缺少参数');
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
                $this->returnError('10001', '排课时间有误');
            }
            // 课程时长分钟数
            $cur_min = db('curriculums')->where('cur_id', '=', $cur_id)->value('ctime');

            $end_time = $data['cur_time'] + $cur_min * 60;
            $start_time = $data['cur_time'];
            $res = db('teach_schedules')->whereTime('cur_time', [$start_time, $end_time])->select();

            if(!empty($res))
            {
                $this->returnError( '20002', '调课失败');
            }

        }
        try{
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update($data);
            $this->returnData(1, '', '调课成功');
        }catch (Exception $e)
        {
            $this->returnError('20002', '调课失败');
        }
    }

    /*
     * 课程顺延
     */
    public function schedulePostpone()
    {
        $this->auth_get_token();
        $sc_id = input('sc_id/d', '');
        if(empty($sc_id))
        {
            $this->returnError(0, '', '缺少参数');
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
                $this->returnError('20002', '顺延失败, 课程冲突');
            }
            // 更新上课时间
            db('teach_schedules')->where('sc_id', '=', $sc_id)->update(['cur_time' => $start_time]);
            $this->returnData(1,  '顺延成功');
        }catch (Exception $e)
        {
            $this->returnError('50000', '服务器错误');
        }
    }

    /**
     * 请假
     */
    public function updatePending()
    {
        $this->auth_get_token();
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
            $this->returnError( '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
            ->update(['level'=> $level, 'status'=>$status]);
            $this->returnData(1,  '请假成功');
        }catch (Exception $e)
        {
            $this->returnError(0, '服务器错误，请假失败');
        }
    }

    /**
     * 取消请假状态
     */
    public function cancelPending()
    {
        $this->auth_get_token();
        $status = 1;  //　设为正常状态
        $sc_id = input('sc_id', '');
        $level = 0; // 1事假, 2病假, 3老师请假, 0 非请假
        if(empty($sc_id))
        {
            $this->returnError( '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['level'=> $level, 'status'=>$status]);
            $this->returnData(1,  '取消请假成功');
        }catch (Exception $e)
        {
            $this->returnError(0, '服务器错误，请假失败');
        }
    }

    /**
     * 旷课
     */
    public function updateTruancy()
    {
        $this->auth_get_token();
        $status = 4;  //　设为旷课状态
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
        {
            $this->returnError( '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['status'=>$status]);
            $this->returnData(1,  '取消请假成功');
        }catch (Exception $e)
        {
            $this->returnError(0,  '服务器错误，　请假失败');
        }
    }

    /*
     * 取消旷课
     */
    public function cancelTruancy()
    {
        $this->auth_get_token();
        $status = 1;  //　设为正常状态
        $sc_id = input('sc_id', '');
        if(empty($sc_id))
        {
            $this->returnError( '10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['status'=>$status]);
            $this->returnData(1,  '取消旷课成功');
        }catch (Exception $e)
        {
            $this->returnError( '50000', '服务器错误，　请假失败');
        }
    }

    /*
     * 还原默认状态
     */
    public function scheduleRecover()
    {
        $this->auth_get_token();
        $status = 1;  //　设为正常状态
        $sc_id = input('sc_id/d', '');
        if(empty($sc_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        try
        {
            db('teach_schedules')->where('sc_id', '=', $sc_id)
                ->update(['status'=>$status]);
            $this->returnData(1,  '还原成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '服务器错误，还原失败');
        }
    }


    /**
     * 课程列表
     */
    public function course()
    {
        $this->auth_get_token();
        $orgid = input('orgid');
        if (empty($orgid))
        {
            $this->returnError( '10000', '缺少参数orgid');
        }
        $data = db('subjects')->where('pid', 0)->field('sid, sname')->order('create_time DESC')->select();
        foreach ($data as $k=>$v) {
            $temp = db('curriculums')->
                    field('cur_id, cur_name')->
                    where(['orgid'=>$orgid, 'subject'=>$v['sid'], 'is_del'=>0])->select();
            $data[$k]['courses']=$temp;
            unset($temp);
        }
        $this->returnData( $data, '');
    }

    /*
     * 教师下拉列表
     */
    public function selectedTeacher()
    {
        $this->auth_get_token();
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->returnError('10000','缺少参数');
        }
        $data = db('teachers')->field('t_id, t_name')->where(['org_id'=>$org_id, 'is_del'=>0])->select();
        $this->returnData($data , '请求成功');
    }

    /*
     * 教室下拉列表
     */
    public function selectedRoom()
    {
        $this->auth_get_token();
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->returnError( '10000','缺少参数');
        }
        $data = db('classrooms')->field('room_id, room_name')->where('or_id','=', $org_id)->select();
        $this->returnData($data,'请求成功');
    }

    /*
     * 课程薪酬方式
     */
    public function salary_pay_type()
    {
        $this->auth_get_token();
        $orgid = input('orgid/d', '');
        if(empty($orgid))
        {
            $this->returnError('10000', '缺少参数');
        }
        $data = db('pay_info')->field('pay_id_info as p_id, pay_name as p_name')->select();
        $this->returnData( $data, '请求成功');
    }
}

