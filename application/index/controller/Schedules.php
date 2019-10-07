<?php


namespace app\index\controller;
use app\index\model\Curriculums;
use app\index\model\Purchase_Lessons as Plessons;
use app\index\model\Purchase_Lessons;
use app\index\model\Schedule;
use app\index\model\TSchedulesHistory;
use app\index\validate\Teachers;
use think\console\Table;
use think\Db;
use think\db\Where;
use think\Exception;
use think\facade\Request;
use think\Model;
use app\index\model\Students;
class Schedules extends BaseController
{
    /*
     * 老师排课
     */
    public function add_teacher_schedule()
    {
        $or_id = Request::instance()->header()['orgid'];  //从header里面拿orgid
        $data = input('post.');
        $data['or_id'] = $or_id;
        $t_id = input('post.t_id');
        $cur_id = input('post.cur_id');
        $cur_durtion = Curriculums::where('cur_id', $cur_id)->find()['ctime'];
        $pitch_num = input('post.pitch_num');
        //获得stu_id 对应的 学生姓名1/学生姓名2/学生姓名3
        $stu_id = input('stu_id');
        $stu_id_array = explode(',', $stu_id);
//
//        $stu_name_array=Students::field('truename')
//               ->where('stu_id','in',$stu_id_array)->select()->toArray();
////        var_dump($stu_name_array);
//        $stu_name=implode(', ',array_column($stu_name_array,'truename'));
        //计算这个学生买的课的节数
        foreach ($stu_id_array as $item) {
            $total_class_hour = Purchase_Lessons::where(['or_id' => $or_id, 'stu_id' => $item, 'cur_id' => $cur_id])->sum('class_hour');
            if ($total_class_hour < $pitch_num) {
                $this->returnError(70000, '排课课时' . $pitch_num . '超过课程购买课时' . $total_class_hour);
            }
        }
        $pl_array = Purchase_Lessons::field('id,class_hour,single_price')->where(['or_id' => $or_id, 'stu_id' => input('post.stu_id'), 'cur_id' => $cur_id])->order('create_time')->select();

        Db::startTrans();
        try {
            $history = TSchedulesHistory::create($data);
            // validate data.
//        $validate=new Schedule();
//        if(!$validate->scene('add')->check($data)){
////            var_dump($validate->getError());
//            $error = explode('|', $validate->getError());
////            var_dump($error);
//            $this->return_data(0, $error[1], $error[0]);
//        }
            $start_time = input('post.start_time');
            //获取今日开始时间戳
            $today_start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            if ($start_time < $today_start) {
                $start_time = $today_start;
            }
            $type = input('post.type');
            //将开始时间移到周几开始的时间
            $day = input('post.day');
            $sub = date('w', $start_time) - $day;
            if ($sub > 0) {
                $start_time += (7 - $sub) * 24 * 60 * 60;
            } else {
                $start_time += $sub * 24 * 60 * 60;
            }

            //扣购课的课时,先扣最先购买的记录
            foreach ($pl_array as $key => $value) {

                if ($pitch_num - $value['class_hour'] >= 0) {    //一次购课不够，排不完，需要第二个购课记录
                    //for循环一节一节添加
//                    var_dump($value);
                    for ($n = 0; $n < $value['class_hour']; $n++) {
                        //3种排课类型相隔天数不同
                        switch ($type) {
                            case 0:   //每天
                                $schedule['cur_time'] = $start_time + input('post.day_time') + $n * 24 * 60 * 60;
                                break;
                            case 1:  //每周
                                $schedule['cur_time'] = $start_time + input('post.day_time') + $n * 24 * 60 * 60 * 7;
                                break;
                            case 2:  //隔周
                                $schedule['cur_time'] = $start_time + input('post.day_time') + $n * 24 * 60 * 60 * 7 * 2;
                                break;
                        }
                        if ($n = $value['class_hour']) {
                            TSchedulesHistory::where('id', $history['id'])->update(['next_time', $schedule['cur_time']]);
                            break;
                        }
                        $schedule = [
                            'stu_id' => input('post.stu_id'),
                            't_id' => $t_id,
                            'room_id' => input('post.room_id'),
                            'cur_id' => $cur_id,
                            'type' => input('post.c_type'),
                            'org_id' => $or_id,
                            'th_id' => $history['id'],
                            'bug_id' => $value['id'],
                            'order' => $n + 1,
                            'day' => $day,
                            'cost' => $value['single_price'],
//                            'stu_name'=>$stu_name
                        ];

                        $schedule['end_time'] = $schedule['cur_time'] + $cur_durtion * 60;
//            var_dump($schedule['end_time']);
//            var_dump($schedule['cur_time']+$cur_durtion*60);
                    $this->check_conflict($t_id,$schedule,$stu_id_array);
                    Schedule::create($schedule);

                    }
                    $pitch_num = $pitch_num - $value['class_hour'];

                    Purchase_Lessons::where('id', $schedule['bug_id'])->update(['surplus_hour' => 0]);
                } else { //这次要排完了
                    //for循环一节一节添加
                    for ($n = 0; $n < $pitch_num; $n++) {
                        $schedule = [
                            'stu_id' => input('post.stu_id'),
                            't_id' => $t_id,
                            'room_id' => input('post.room_id'),
                            'cur_id' => $cur_id,
                            'type' => input('post.c_type'),
                            'org_id' => $or_id,
                            'th_id' => $history['id'],
                            'bug_id' => $value['id'],
                            'order' => $n + 1,
                            'day' => $day,
                            'cost' => $value['single_price']
                        ];
                        //3种排课类型相隔天数不同
                        switch ($type) {
                            case 0:   //每天
                                $schedule['cur_time'] = $start_time + input('post.day_time') + $n * 24 * 60 * 60;
                                break;
                            case 1:  //每周
                                $schedule['cur_time'] = $start_time + input('post.day_time') + $n * 24 * 60 * 60 * 7;
                                break;
                            case 2:  //隔周
                                $schedule['cur_time'] = $start_time + input('post.day_time') + $n * 24 * 60 * 60 * 7 * 2;
                                break;
                        }
                        $schedule['end_time'] = $schedule['cur_time'] + $cur_durtion * 60;
//            var_dump($schedule['end_time']);
//            var_dump($schedule['cur_time']+$cur_durtion*60);
                        $this->check_conflict($t_id, $schedule, $stu_id_array);


                        Schedule::create($schedule);
                    }

                    Purchase_Lessons::where('id', $schedule['bug_id'])->update(['surplus_hour' => $value['class_hour'] - $pitch_num]);
                    break;
                }

            }


            Db::commit();
            return $this->returnData('', "排课成功");
        } catch (Exception $exception) {
            Db::rollback();
            $this->returnError("", $exception->getMessage());
        }


    }

    /**
     * 获得学生待排课
     */
    public function get_ready_arrange_cur()
    {
//       $stu_id=Plessons::
    }

    /**
     * 获得待排课的列表
     * @param stu_name学生姓名
     * @param tea_name 老师姓名
     * @param cur_name 课程姓名
     */
    public function get_ready_arrange_stu()
    {
        $or_id = Request::instance()->header()['orgid'];  //从header里面拿orgid
        $data = null;
        $stu_name = input('get.stu_name');
        $cur_name = input('get.cur_name');
        $tea_name = input('get.tea_name');
        $limit = input('get.limit', 20);
        if ($tea_name) {
            $where[] = ['d.t_name', 'like', '%' . $tea_name . '%'];
        }
        if ($stu_name) {
            $where[] = ['b.truename', 'like', '%' . $stu_name . '%'];
        }
        if ($cur_name) {
            $where[] = ['c.cur_name', 'like', '%' . $cur_name . '%'];
        }
        //机构和排课数量不为0的，0是已经排完的
        $where0['or_id'] = ['=', $or_id];
        $where0['class_hour'] = ['<>', 0];
        $data = Plessons::where($where0)->alias('a')
//           ->join('erp2_teachers d ','d.t_id=a.t_id')
            ->join('erp2_students b', 'a.stu_id=b.stu_id')
            ->join('erp2_curriculums c', 'c.cur_id=a.cur_id')
            ->field('a.id,c.cur_name,b.truename,c.tmethods,original_price,a.class_hour');
        if (isset($where)) {
            $data = $data->where($where);
        }
        $data = $data->paginate($limit);
        $this->returnData($data, "");
    }

    /**
     * 老师课表
     */
    public function get_tea_schedules()
    {
        $start_time = input('start_time');
        $end_time = input('end_time');
        $map['a.t_id'] = input('t_id');
        $map['a.is_del'] = 0;
        $subject = input('subject');
        if ($subject != null)
            $map['d.subject'] = $subject;
        $curid = input('cur_id');
        if ($curid != null)
            $map['d.cur_id'] = $curid;
        $data = Schedule::where($map)->alias('a')
            ->join('erp2_teachers b', 'a.t_id=b.t_id')
            ->join('erp2_curriculums d', 'a.cur_id=d.cur_id')
            ->join('erp2_classrooms e', 'a.room_id=e.room_id')
            ->field('sc_id,a.order,a.cost,b.t_name,a.stu_id,cur_time,d.cur_name,d.tmethods,end_time,e.room_name,a.day')
            ->whereTime('cur_time', '<=', $end_time)
            ->whereTime('cur_time', '>=', $start_time)
//            ->group('a.day')
            ->select();
        $data1 = array();
        for ($n = 1; $n < 8; $n++) {
            $day_object = null;
            $day_a = array();
            foreach ($data as $datum) {
                if ($datum['day'] == $n) {

                    $gt = Schedule::where('cur_time', '>', $datum['cur_time'])->find();  //处理是不是最后一节课
                    if ($gt != NULL) {
                        $datum['is_last'] = false;
                    } else {
                        $datum['is_last'] = true;
                    }
                    $stu_id_array = explode(',', $datum['stu_id']);

                    $stu_name_array = Students::field('stu_id,truename')
                        ->where('stu_id', 'in', $stu_id_array)->select()->toArray();
                    $datum['student'] = $stu_name_array;
                    array_push($day_a, $datum);

                }

            }
            $day_object['tb'] = $day_a;
            array_push($data1, $day_object);
        }

        return $this->returnData($data1, "");
    }

    /**
     * 获得学生可以排的课的列表
     */
    public function get_can_arrange_cur()
    {
        $or_id = Request::instance()->header()['orgid'];  //从header里面拿orgid
        $data = Purchase_Lessons::field('b.cur_id,b.cur_name')->alias('a')
            ->join('erp2_curriculums b', 'a.cur_id=b.cur_id')
            ->where(['or_id' => $or_id, 'stu_id' => input('stu_id')])
            ->distinct(true)
            ->select();
        $this->returnData($data, "");
    }

    /**
     * 获得今日课表
     */
    public function today_schedule()
    {
        $start_time = input('start_time');
        $end_time = input('end_time');
        $map['a.is_del'] = 0;
        $subject = input('subject');
        $or_id= Request::instance()->header()['orgid'];  //从header里面拿orgid

        if ($subject != null)
            $map['d.subject'] = $subject;
        $curid = input('cur_id');
        if ($curid != null)
            $map['d.cur_id'] = $curid;
        $teachers=\app\index\model\Teacher::where('org_id',$or_id)->select();

        $data1 = array();
        foreach ($teachers as $t){
            $day_object = null;
            $day_a = array();
            $map['a.t_id'] = $t['t_id'];
            $data = Schedule::where($map)->alias('a')
                ->join('erp2_curriculums d', 'a.cur_id=d.cur_id')
                ->join('erp2_classrooms e', 'a.room_id=e.room_id')
                ->field('sc_id,a.order,a.cost,a.stu_id,cur_time,d.cur_name,d.tmethods,end_time,e.room_name,a.day')
                ->whereTime('cur_time', '<=', $end_time)
                ->whereTime('cur_time', '>=', $start_time)
//            ->group('a.day')
                ->select();
            foreach ($data as $datum) {
                    $gt = Schedule::where('cur_time', '>', $datum['cur_time'])->find();  //处理是不是最后一节课
                    if ($gt != NULL) {
                        $datum['is_last'] = false;
                    } else {
                        $datum['is_last'] = true;
                    }
                    $stu_id_array = explode(',', $datum['stu_id']);

                    $stu_name_array = Students::field('stu_id,truename')
                        ->where('stu_id', 'in', $stu_id_array)->select()->toArray();
                    $datum['student'] = $stu_name_array;
                    array_push($day_a, $datum);



            }
            $day_object[$t['t_name']] = $day_a;
            array_push($data1, $day_object);
        }

        return $this->returnData($data1, "");
    }

    /**
     * 课程的排课历史信息
     */
    public function tchedule_history()
    {
//        $or_id= Request::instance()->header()['orgid'];  //从header里面拿orgid
//        $map['org_id']=$or_id;
        $map['cur_id'] = input('cur_id');
        $data = db('tschedules_history')->where($map)
            ->where('FIND_IN_SET(:stu_id,stu_id)', ['stu_id' => input('stu_id')])
            ->field('type,day,day_time,next_time,pitch_num')
            ->select();
        return $this->returnData($data, '');
    }

    /**
     * 根据课程id获得学生购这门课的信息
     */
    public function pl_course_info()
    {
        $stu_id = input('stu_id');
        $cur_id = input('cur_id');
        $data = Purchase_Lessons::field('c.ctime,sum(a.surplus_hour) as surplus_hour,sum(a.class_hour) as class_hour')
            ->alias('a')
            ->where(['a.stu_id' => $stu_id, 'a.cur_id' => $cur_id])
//          ->join('erp2_teachers b','a.t_id=b.t_id')
            ->join('erp2_curriculums c', 'c.cur_id=a.cur_id')
            ->group('a.stu_id,a.cur_id')
            ->select();

        $this->returnData($data, "");
    }

    /**
     * 获得老师一对多课程
     */
    public function teacher_cur_n()
    {
        $data = db('cur_teacher_relations')
            ->alias('a')
            ->where(['a.t_id' => input('t_id'), 'b.tmethods' => 2])
            ->join('erp2_teachers c', 'c.t_id=a.t_id')
            ->join('erp2_curriculums b', 'b.cur_id=a.cur_id')
            ->field('b.cur_id,b.cur_name')
            ->select();
        $this->returnData($data, '');
    }

    /**
     * 获得一对多的学生购买的课程列表
     */
    public function student_bc_list()
    {
        $cur_id = input('cur_id');
        $truename = input('truename');
        $map[] = ['a.surplus_hour', '<>', 0];
        $map[] = ['a.cur_id', '=', $cur_id];
        $map[] = ['c.tmethods', '=', 2];
        if ($truename != null) {
            $map[] = ['b.truename', 'like', '%' . $truename . '%'];
        }
        $data = Purchase_Lessons::field('b.truename,a.single_price,a.give_class,a.surplus_hour,a.class_hour')
            ->alias('a')
            ->where($map)
            ->join('erp2_students b', 'a.stu_id=b.stu_id')
            ->join('erp2_curriculums c', 'c.cur_id=a.cur_id')
            ->select();
        $this->returnData($data, "");
    }

    /**
     * 删除这节排课
     */
    public function del_schedule()
    {
        Schedule::where('sc_id', input('sc_id'))->update(['is_del' => 1]);
        $this->returnData('', '删除成功！');
    }

    /**
     * 删除这节排课之后的所有排课
     */
    public function del_schedule_after()
    {
        $sc_id = input('sc_id');
        if ($sc_id == null) {
            $this->returnError('10000', '缺少必填参数sc_id');
        }
        Db::startTrans();
        try {
            $schedule = Schedule::where('sc_id', $sc_id)->find();
//            var_dump($schedule);
            Schedule::where([['th_id', '=', $schedule['th_id']], ['cur_time', '>=', $schedule['cur_time']]])
                ->update(['is_del' => 1]);
            Db::commit();
            $this->returnData('', '删除成功！');
        } catch (Exception $e) {

            Db::rollback();
            $this->returnError('20003', $e->getMessage());
        }


    }
//    /**
//     * 获取单个课程信息
//     */
//    public function  get_schedule(){
//        $schedule=''
//        retrurn_
//    }
    /**
     * 调课
     */
    public function update_schedule()
    {
        $sc_id = input('sc_id');
        $cur_time = input('cur_time');
        $room_id = input('room_id');
        $t_id = input('t_id');
        $end_time=input('end_time');
        $stu_id=input('stu_id');
        if ($stu_id == null) {
            $this->returnError('10000', '缺少必填参数stu_id');
        }
        $stu_id_array = explode(',', $stu_id);
        if ($sc_id == null) {
            $this->returnError('10000', '缺少必填参数sc_id');
        }
        if ($cur_time != null&$end_time!=null){
            $map['cur_time'] = $cur_time;
            $map['end_time']=$end_time;
           $this->check_conflict($t_id,$map,$stu_id_array);

        }

        if ($t_id)
            $map['t_id'] = $t_id;
        if ($room_id != null)
            $map['room_id'] = $room_id;
        Db::startTrans();
        try {
            Schedule::where('sc_id', $sc_id)->update($map);
            $data['time'] = time();
            $data['reason'] = input('reason');
            $data['sc_id'] = $sc_id;
            Db::name('schedule_adjust_record')->insert($data);
            Db::commit();
            $this->returnData("", '调课成功！');
        } catch (Exception $e) {
            Db::rollback();
            $this->returnError('20002', $e->getMessage());
        }

    }

    /*
     * 查询调课记录
     */
    public function adjust_schedule_record()
    {
        $sc_id = input('sc_id');
        if ($sc_id == null) {
            $this->returnError('10000', '学生必填参数sc_id');
        }
        $data = Db::name('schedule_adjust_record')
            ->where('sc_id', input('sc_id'))
            ->select();
        $this->returnData($data, '');
    }

    /**
     * @param $t_id
     * @param array $schedule
     * @param array $stu_id_array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check_conflict($t_id, array $schedule, array $stu_id_array)
    {
//检查老师课程是不是冲突
        $exist_schedule = Schedule::where('t_id', $t_id)
            ->where([
                ['cur_time', '>=', $schedule['cur_time']],
                ['cur_time', '<=', $schedule['end_time']]])
            ->whereOr('end_time', ['>=', $schedule['cur_time']], ['<=', $schedule['end_time']], 'and')
            ->find();
//            var_dump($schedule['end_time'].'  curtime '.$schedule['cur_time'].$exist_schedule);
        if ($exist_schedule == NULL) {
        } else {
            $this->returnError(20001, "老师当前时间段有课，冲突");
        }
        //检查学生课程是不是冲突

        foreach ($stu_id_array as $item) {
            $exist_schedule = Schedule::where('FIND_IN_SET(:stu_id,stu_id)', ['stu_id' => $item])
                ->where([
                    ['cur_time', '>=', $schedule['cur_time']],
                    ['cur_time', '<=', $schedule['end_time']]])
                ->whereOr('end_time', ['>=', $schedule['cur_time']], ['<=', $schedule['end_time']], 'and')
                ->find();
//            var_dump($schedule['end_time'].'  curtime '.$schedule['cur_time'].$exist_schedule);
            if ($exist_schedule == NULL) {
            } else {
                $this->returnError(20001, "学生当前时间段有课，冲突");
            }
        }
    }
}