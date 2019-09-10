<?php

namespace app\index\controller;
use app\index\model\Classroom as ClsModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use think\Controller;
use think\Db;
use think\Exception;
use PHPExcel;
use think\Log;
/*
* Basic Controller Provides Information Interface for Import, Export and Return
*/
class ExcelBase extends Controller
{
    /**
     * Return error's code and error's message.
     * @param $error_code
     * @param $error_msg
     */
    public function returnError($error_code, $error_msg)
    {
        $data = [
            'status' => false,
            'error_code' => $error_code,
            'error_msg' => $error_msg,
            'data' => ''
        ];
        echo json_encode($data);
        exit();
    }

    /**
     * Returns the status and data of the successful request
     * @param $info
     * @param $data
     */
    public function returnData($info, $data)
    {
        $data = [
            'status' => true,
            'sinfo' => $info,
            'error_code' => '',
            'data' => $data
        ];
        echo json_encode($data);
        exit();
    }

    /**
     * General excel import method
     * @param $expTitle
     * @param $expCellName
     * @param $expTableData
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function exportExcel($expTitle,$expCellName,$expTableData){

        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);

        $fileName = $xlsTitle . date('_YmdHis') . '.xlsx';

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $objPHPExcel->getActiveSheet()->setTitle($xlsTitle);
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');

        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }

        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        ob_end_clean();
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    /*
     * Read data from EXCEL into an array
     */
    public function getExcelData($file)
    {
        // save excel file to path: public/uploads/excel/
        $dirPath = "./upload/file/";
        $info = $file->validate(['size'=>104857600,'ext'=>'xls,xlsx'])->move( $dirPath);
        if($info){
            $fileName = $info->getSaveName();
            $filePath = $dirPath . $fileName;
            $suffix = $info->getExtension();

            if($suffix=="xlsx"){
                $reader = \PHPExcel_IOFactory::createReader('Excel2007');
            }else{
                $reader = \PHPExcel_IOFactory::createReader('Excel5');
            }
        }else{
            $this->returnError('30000', '上传失败');
        }

        $excel = $reader->load("$filePath", $encode = 'utf-8');
        $data = $excel->getSheet(0)->toArray();
        array_shift($data);
        return $data;
    }

    //通用导出方法
    public function export($filename,$expCellName,$expTableData){
        //1.从数据库中取出数据
        //3.实例化PHPExcel类
        $objPHPExcel = new PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        //创建颜色对象，设置颜色像css那样简单的传个色值，需要传对象
        $styleArray = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FF0000'),
                'size'  => 15,
                'name'  => 'Verdana'
            ));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->applyFromArray($styleArray);
        }
        //设置宽高
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(30);
        }
        //设置第二行内容
//        for($i=0;$i<$cellNum;$i++){
//            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][0]);
//        }
        //循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<$dataNum;$i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        //7.设置保存的Excel表格名称
        $filename = $filename.date('ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('学生信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }

    /*
     * Get the uploaded excel file path
     */
    public function getExcelPath($request)
    {
        $file = $request->file('excel');
        // save excel file to path: public/uploads/excel/
        $info = $file->validate(['size'=>1048576,'ext'=>'xls, xlsx'])->move( UPLOAD_DIR . 'excel/');
        if($info)
        {
            $fileName = $info->getSaveName();
            $filePath = UPLOAD_DIR.'excel/'. $fileName;
            return $filePath;
        }
        else {
            $this->returnError('30000', '文件上传失败');
        }
    }

    /*
     * validate date.
     */
    public function validate_date($date)
    {
        $pattern = "/^\d{4}\/\d{1,2}\/\d{1,2}$/";
        if (!preg_match($pattern, $date))
        {
            return false;
        }
        $t = explode('/', $date);
        if (checkdate($t[1], $t[2], $t[0]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /*
     * 读取excel 单元格yyyy/mm/dd 会自动转换为 dd/mm/yyyy
     * 将读取excel的时间字段转换为正常格式
     */
    public function trans_date($date)
    {
        $data = explode('/', $date);
        $data = array_reverse($data);
        $str = implode('/', $data);
        return $str;
    }
}

/*
 * All excel import and export template download classes
 */
class Excel extends ExcelBase
{
    /*
     * Template Download for classroom information import
     */
    public function room_tpl()
    {
        $str = "./public/upload/file/classroom.xlsx";
        $this->returnData('', $str);
    }

    // Classroom information introduction method
    public function room_ipt(){
        $uid = input('uid', '');
        $org_id = input('orgid', '');
        if (empty($uid) || empty($org_id))
        {
            $this->returnError('10000', '缺少参数uid或orgid');
            exit();
        }
        $sql = "SELECT B.or_id as org_id FROM erp2_users AS A INNER JOIN 
erp2_organizations AS B ON A.organization=B.or_id WHERE A.uid={$uid} LIMIT 1;";
        $temp = Db::query($sql);

        if (empty($temp) || $temp[0]['org_id'] != $org_id)
        {
            $this->returnError('10000', '请求非法');
        }
        $file = request()->file('excel');
        if (empty($file))
        {
            $this->returnError(10000, '缺少上传文件excel.');
            exit();
        }
        $excel_data = $this->getExcelData($file);

        Db::startTrans();
        try {
            $response = [];
            foreach ($excel_data as $val)
            {
                $data['room_name'] = trim($val[0]);
                $data['room_count'] = trim($val[1]);
                if(!is_numeric($data['room_count']))
                {
                    $this->returnError('10001', '数据有误');
                    exit();
                }
                if(empty($data['room_name'] || empty($data['room_count'])))
                {
                    $this->returnError('10000', '教室人数和教室名称不能为空。');
                    exit();
                }
                if(strlen($data['room_name']) > 40)
                {
                    $this->returnError('10000', '教室名称字符过长');
                    exit();
                }
                if ($data['room_count'] > 500 || $data['room_count'] == 0)
                {
                    $this->returnError('10000', '教室容量在[1-500]之间');
                    exit();
                }
                if ($val[2] == 2)
                {
                    $data['status'] = $val;
                }
                else
                {
                    $data['status'] = 1;
                }
                $data['manager'] = $uid;
                $data['or_id'] = $org_id;

                $count = Db::table('erp2_classrooms')->where(
                        ['or_id'=>$org_id, 'room_name'=>$data['room_name'], 'is_del'=>0]
                )->count();
                if($count > 0)
                {
                    array_push($response, $data['room_name']);
                }
                else
                {
                    $data['create_time'] = time();
                    Db::table('erp2_classrooms')->insert($data);
                }
                unset($data);
            }
            // 提交事务
            Db::commit();
            $this->returnData('导入成功', $response);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->returnError('20003', '导入失败');
            exit();
        }
    }

    /*
     * Classroom Information Exporting Method
     */
    public function room_ept(){
        $org_id = input('orgid/d', '');
        if (empty($org_id))
        {
            $this->returnError('10000', '缺少参数orgid');
            exit();
        }
        $xlsName  = "classroom";
        $xlsCell  = array(
            array('name', '教室名称(必填)'),
            array('count','容纳人数(必填)'),
            array('status','教室状态(1可用，２不可用)'),
        );
        if (empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        $where[] = ['or_id', '=', $org_id];
        $where[] = ['is_del', '=', 0];
        $xlsData = db('classrooms')->
                    where($where)->
                    field(' room_name as name, room_count as count, status')->select();

        $this->export($xlsName,$xlsCell,$xlsData);
    }


    /*
     * Template Download for teacher information import
     */
    public function teacher_tpl()
    {
        $str = "./public/uploads/file/teacher.xlsx";
        $this->returnData('', $str);
    }


    // Teacher Information Exporting Method
    public function teacher_ept(){
        $org_id = input('orgid/d', '');
        $se_id = input('se_id/d', ''); // 资历ID
        $status = input('status/d', '');  // 离职状态
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数org_id');
        }
        $xls_name  = "教师信息" . date('Y-m-d', time());
        $xls_cell  = array(
            array('t_name', '姓名(必填)'),
            array('t_sex','性别(只能填男或者女)'),
            array('t_sen', '资历(暂时无需填写，导入后进入系统修改)'),
            array('t_mobile', '手机号(此项必填)'),
            array('t_entry_time', '入职日期(格式必须为：1970/01/01)'),
            array('t_id_card', '身份证(必填)'),
            array('t_birthday', '生日(格式为:1970/01/01)'),
            array('t_resume', '简历(非必填,最多2000字)'),
            array('t_status', '教师状态: 1在职, 2离职')
        );

        $teachers = Db::name('teachers')->field('t_name, sex as t_sex, se_id, 
                        identity_card as t_id_card, cellphone as t_mobile,
                        birthday as t_birthday, resume as t_resume, status as t_status, entry_time as t_entry_time');
        $where[] = ['org_id', '=', $org_id];
        if(!empty($se_id))
        {
            if(!is_numeric($se_id) || $se_id < 0)
            {
                $this->returnError('10000', '资历id不合法');
            }
            $where[] = ['se_id', '=', $se_id];
        }
        if(!empty($status))
        {
            if(!is_numeric($se_id) || $status < 0)
            {
                $this->returnError('10000', 'status不合法');
            }
            $where[] = ['status', '=', $status];
        }
        $result = $teachers->where($where)->select();

        $xls_data = [];
        foreach ($result as $k=>$v)
        {
            $v['t_sex'] = $v['t_sex'] == 1 ? '男' : '女';
            $v['t_sen'] = Db::name('seniorities')->where('seniority_id', '=', $v['se_id'])
                            ->value('seniority_name');
            $v['t_birthday'] = date('Y/m/d', $v['t_birthday']);
            $v['t_entry_time'] = date('Y/m/d', $v['t_entry_time']);
            unset($v['se_id']);
            $xls_data[] = $v;
            unset($v);
        }
//        $this->returnData('请求成功', $xls_data);
        $this->export($xls_name, $xls_cell, $xls_data);
    }

    /**
     * Teacher information introduction method
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function teacher_ipt(){
        $org_id = input('orgid', '');
        $uid = input('uid', '');
        try {
            $file = request()->file('excel');
        }catch (Exception $e)
        {
            if (empty($file))
            {
                $this->returnError(10000, '缺少文件');
            }
        }
        if (empty($org_id))
        {
            $this->returnError('10000', '缺少orgid');
        }

        $data = $this->getExcelData($file);

        Db::startTrans();
        try{
            foreach ($data as $k => $v)
            {
                $t['t_name'] = $v[0];
                $t['sex'] = $v[1];
                $t['se_id'] = 1;
                $t['cellphone'] = $v[3];
                $t['entry_time'] = $this->trans_date($v[4]);
                $t['identity_card'] = $v[5];
                $t['birthday'] = $this->trans_date($v[6]);
                $t['resume'] = $v[7];
                $t['status'] = $v[8];
                $t['manager'] = $uid;
                $t['org_id'] = $org_id;
                if (empty($t['t_name']))
                {
                    $this->returnError(10000, '姓名不能为空');
                }
                if ($t['t_name'] > 20 )
                {
                    $this->returnError('10000', '教师名称大于10个字符');
                }
                if (!in_array($t['sex'], ['男', '女']))
                {
                    $this->returnError('10000', '性别只能是男, 女: ');
                }

                if (!preg_match("/^1[345789]\d{9}$/", $t['cellphone'], $matches))
                {
                    Db::rollback();
                    $this->returnError('10000', '手机号码有误');
                }
                if (!$this->validate_date($t['entry_time']))
                {
                    Db::rollback();
                    $this->returnError('10000', '入职日期格式错误');
                }
                if (!$this->validate_date($t['birthday']))
                {

                    Db::rollback();
                    $this->returnError('10000', '生日日期格式错误' . $v['birthday']);
                }
                $card_pattern ='/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i';
                if(!preg_match($card_pattern, $t['identity_card']))
                {
                    Db::rollback();
                    $this->returnError('10000', '身份证格式错误');
                }
                if ($t['resume'] == null)
                {
                    $t['resume'] = '';
                }
                if(!is_numeric($t['status']) and !in_array($t['status'], [1, 2]))
                {
                    $t['status'] = 1;
                }
                $t['sex'] = $t['sex'] == '男' ? 1 : 2;
                $t['entry_time'] = strtotime($t['entry_time']);
                $t['birthday'] = strtotime($t['birthday']);
                $t_id = Db::table('erp2_teachers')->insertGetId($t);

                // 添加教师薪酬
                Db::name('teacher_salary')->insert(['t_id'=>$t_id]);
                unset($t, $v);
            }

            Db::commit();

            $this->returnData('导入成功', true);
        }catch (\Exception $e){
            Db::rollback();
            $this->returnError('20001', $e->getMessage());
        }

    }


    /**
     * Download Data Template for Course Purchase
     */
    public function schedule_tpl()
    {
        $str = "./public/uploads/file/schedule.xlsx";
        $this->returnData('', $str);
    }

    /**
     * Importing Purchasing Course Data from EXCEL
     */
    public function schedule_ipt()
    {
        $org_id = input('orgid', '');
        $uid = input('uid', '');
        $file = request()->file('excel');
        if (empty($org_id) || empty($file))
        {
            $this->returnError('10000', '缺少文件或者orgid');
        }
        $data = $this->getExcelData($file);

        foreach ($data as $k=>$v)
        {
            $cur_name = $v[0];
            $cur_day = $v[1];
            $cur_time = $v[2];
            $stu_name = trim($v[3]);
            $room_name = $v[4];
            $status = $v[5];
            if (strlen($cur_name) > 40)
            {
                $this->returnError('10000', '课程名称过长');
            }
            if(preg_match('\d{4}\/d{2}\/d{2}', $cur_day))
            {
                $this->returnError('10000', '上课日期格式错误');
            }
            if(preg_match('\d{2}:\d{2}', $cur_time))
            {
                $this->returnError('10000', '上课时间格式错误');
            }
            if(strlen($stu_name) > 40) {
                $this->returnError('10000', '学生姓名过长');
            }
            if(strlen($room_name))
            {
                $this->returnError('10000', '教室名称过长');
            }
            if($status != 2 || $status != 1)
            {
                $status = 1;
            }
            $stu_id = db('students')->where('truename', '=', $stu_name)->value('stu_id');
            if(empty($stu_id))
            {
                $this->returnError('20001', '学生:'. $stu_name .'不存在');
            }
            $cur_time = strtotime($cur_day . ' ' .$cur_time);
            $cur_id = db('curriculums')->where('cur_name', '=', $cur_name)->value('cur_id');
            if (empty($cur_id))
            {
                $this->returnError('10000', '课程:'. $cur_name .'不存在');
            }
            $room_id = db('classrooms')->where('room_name', '=', $room_name)->value('room_id');
            if(empty($room_id))
            {
                $this->returnError('10000', '教室:'. $room_name .'不存在');
            }
            $in_data = [
                'cur_id' => $cur_id,
                'stu_id' => $stu_id,
                'room_id' => $room_id,
            ];

        }

    }

    /**
     * Data of students'purchasing lessons are exported to EXCEL
     */
    public function schedule_ept()
    {
        $allCode = 1;  // 全部
        $org_id = input('orgid/d', '');
        $curYearCode = 2; // 本年
        $curMonthCode = 3;  // 本月
        $tid = input('t_id/d', '');
        $startTime = input('startTime/d', '');
        $endTime = input('endTime/d', '');
        $type = input('type/d', 1);  // 默认是全部
        $courseId = input('courseId/d', ''); // 通过课程ID筛选

        if (empty($tid) || empty($org_id))
        {
            $this->return_data(0, '10000', '缺少参数');
        }

        $tables = Db::name('teach_schedules')->field('sc_id, stu_id, room_id, cur_time, cur_id, status')
            ->where(['org_id'=>$org_id, 't_id'=>$tid]);

        if (!empty($startTime) and !empty($endTime))
        {
            $tables->whereTime('cur_time','between',[$startTime, $endTime]);
        }else
        {
            if($type==$curYearCode) //查询本年数据
            {
                $tables->whereTime('cur_time', 'year');
            }
            elseif ($type==$curMonthCode){ // 查询本月数据
                $tables->whereTime('cur_time', 'month');
            }
        }
        if(!empty($courseId))
        {
            $tables->where('cur_id', '=', $courseId);
        }
        $data = $tables->select();
        $xls_name  = "教师信息" . date('Y-m-d', time());
        $xls_cell = array(
            array('cur_name', '课程名称(必填)'),
            array('cur_day','上课日期(必填2019/01/02)'),
            array('cur_time', '上课时间(必填08:00)'),
            array('stu_name', '学生姓名(必填)'),
            array('room_name', '教室名称(必填)'),
            array('status', '状态(默认正常，进入系统修改)'),
        );
        $xls_data = array();
        foreach ($data as $k=>$v) {
            $status = $v['status'];
            $sc_id = $v['sc_id'];
            $cur_id = $v['cur_id'];
            $cur_time = $v['cur_time'];
            $stu_id = $v['stu_id'];
            $room_id = $v['room_id'];
            $stu_name = db('students')->where('stu_id', '=', $stu_id)->value('truename');
            $room_name = db('classrooms')->where('room_id', '=', $room_id)->value('room_name');
            $temp = db('curriculums')->where('cur_id', '=', $cur_id)->field('cur_name, 
                        tmethods as cur_type')->find();
            $cur_day = date('Y/m/d', $cur_time);
            $cur_time = \date('H:i:s', $cur_time);
            $xls_data[] = [
                'cur_name' => $temp['cur_name'],
                'cur_day'   => $cur_day,
                'cur_time' => $cur_time,
                'stu_name' => $stu_name,
                'room_name' => $room_name,
                'status' => $status
            ];
        }
        $this->export($xls_name, $xls_cell, $xls_cell);
    }

//    /**
//     * Template Download for student information import
//     */
//    public function stu_tpl()
//    {
//        $str = "./public/upload/file/students.xlsx";
//        $this->returnData('', $str);
//    }

//    /*
//     * Student Information Exporting Method
//     */
//    public function stu_ept()
//    {
//        $org_id = input('org_id', '');
//        if(empty($org_id))
//        {
//            $this->returnError('10000', '缺少参数orgid');
//        }
//        $xlsName  = "学生模板";
//
//        $xlsCell  = array(
//            array('stu_name', '学生姓名(必填)'),
//            array('stu_sex', '性别(必填，男或女)'),
//            array('stu_birthday', '出生日期(如:1996.12.31)'),
//            array('stu_mobile', '手机号(必填)'),
//            array('stu_wechat', '微信号(非必填)'),
//            array('stu_address', '住址(非必填)'),
//            array('stu_remark', ' 备注(非必填)'),
//            array('stu_status', '学生状态')
//        );
//        $sql = "SELECT truename AS stu_name,
//                CASE WHEN sex = 1 THEN '男' WHEN sex = 2 THEN '女' END AS stu_sex,
//                FROM_UNIXTIME(birthday, \"%Y.%m.%d\") AS stu_birthday,
//                cellphone AS stu_mobile, wechat AS stu_wechat, address AS stu_adress,
//                remark AS stu_remark FROM erp2_students WHERE org_id={$org_id}";
//        try{
//            $data = Db::query($sql);
//            $this->exportExcel($xlsName, $xlsCell, $data);
//        }catch (\Exception $e)
//        {
//            $this->returnError('50000', '导出失败');
//        }
//    }
//
//    /*
//     * Student information introduction method
//     */
//    public function stu_ipt()
//    {
//        $org_id = input('org_id', '');
//        $uid = input('uid', '');
//        $file = request()->file('excel');
//        if(empty($org_id) || $file)
//        {
//            $this->returnError(10000, '缺少参数orgid或excel文件');
//        }
//        $data = $this->getExcelData($file);
//        try{
//            foreach ($data as $k=>$v)
//            {
//                $t['manager'] = $uid;
//                $t['org_id'] = $org_id;
//                $t['truename'] = $v[0];
//                $t['sex'] = $v[1] == '男' ? 1 : 2;
//                $t['birthday'] = strtotime($v[2]);
//                $t['cellphone'] = $v[3];
//                $t['wechat'] = $v[4];
//                $t['address'] = $v[5];
//                $t['remark'] = $v[6];
//                Db::table('erp2_students')->insert($t);
//                unset($t);
//            }
//        }catch (\Exception $e){
//            $this->returnError('50000', '插入失败');
//        }
//
//    }


    /*
     * 商品模板
     */
    public function goods_tpl()
    {

    }

    /*
     * 商品导入
     */
    public function goods_ipt()
    {
        $org_id = input('orgid', '');
//        $uid = input('uid', '');
        try {
            $file = request()->file('excel');
        }catch (Exception $e)
        {
            if (empty($file))
            {
                $this->returnError(10000, '缺少文件');
            }
        }
        if (empty($org_id))
        {
            $this->returnError('10000', '缺少orgid');
        }
        Db::startTrans();
        try{

            $data = $this->getExcelData($file);
            $ins_data = [];
            foreach ($data as $k=>$v)
            {
                $goods_name = $v[0];
                $cate_name = $v[1];
                $goods_amount = $v[2];
                $goods_sku = $v[2]?$v[2]:0;
                $unit_name= $v[3];
                $remarks = $v[4];
                if (is_empty($goods_name, $cate_name, $goods_sku, $unit_name))
                {
                    $this->returnError(10000, '缺少参数');
                }
                if (strlen($remarks) > 500)
                {
                    $this->returnError(10001, '备注不能大于5000字');
                }
                $cate_id = db('goods_cate')->
                    field('cate_name', 'like', '%' . $cate_name . '%')->value('cate_id');
                if (empty($cate_id))
                {
                    $this->returnError(10000, '分类不存在');
                }
                $ins_data[] = [
                    'goods_name' => $goods_name,
                    'goods_img' => '',
                    'unit_name' => $unit_name,
                    'remarks' => $remarks,

                ];
            }
        }catch (Exception $e)
        {
            Db::rollback();
            $this->returnError(50000, '系统错误, 导入失败'.$e->getMessage());
        }
    }

    /*
     * 商品导出
     */
    public function goods_ept()
    {
        $org_id = input('orgid' , '');
        $cate_id = input('cate_id/d', '');
        $goods_name = input('goods_name/s', '');
        if(empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $db = db('goods_detail')->field('goods_id, goods_name, remarks,
        unit_name, cate_id, goods_amount, goods_img');
        if (!empty($cate_id))
        {
            $db->where('cate_id', '=', $cate_id);
        }
        if(!empty($goods_name))
        {
            $db->where('goods_name', 'like', '%' . $goods_name . '%');
        }
        $goods_list = $db->order('create_time DESC')->select();
        try
        {
            $data = [];
            foreach ($goods_list as $goods)
            {
                $goods_id = $goods['goods_id'];
                // 分类名称
                $goods['cate_name'] = db('goods_cate')->where(['cate_id'=>$goods['cate_id']])
                    ->value('cate_name');

//            // 入库均价
//            $avg_sql ="SELECT (sum(sto_num*sto_single_price)/sum(sto_num))
//                        as avg_sto_price FROM erp2_goods_storage WHERE goods_id={$goods['goods_id']}";

                // 入库总量
                $sto_total_num = db('goods_storage')->where(['goods_id'=>$goods_id])->sum('sto_num');
//                $this->returnData($sto_total_num);
                $sql = "SELECT sum(sto_single_price * sto_num) as sto_total FROM erp2_goods_storage WHERE goods_id={$goods_id}";
                $res = Db::query($sql)[0]['sto_total'];
                // 入库总额
                $sto_total_money = $res?$res:0;

                // 入库平均单价
                $sto_avg_money = db('goods_storage')->
                where('goods_id','=', $goods_id)->avg('sto_single_price');

                // 出库总量
                $dep_total_num = db('goods_deposit')->where(['goods_id'=>$goods_id])->sum('dep_num');
                // 出库总额
                $sql = "SELECT sum(dep_price*dep_num) as dep_total FROM erp2_goods_deposit WHERE goods_id={$goods_id};";
                $res = Db::query($sql)[0]['dep_total'];
                $dep_total_money = $res ? $res : 0;
//                $dep_total_money = db('goods_deposit')->where(['goods_id'=>$goods_id])->sum('dep_price*dep_num');
                // 出库均价
                $dep_avg_money = db('goods_deposit')->where('goods_id', '=', $goods_id)->avg('dep_price');
                // 销售总额
                $sql = "SELECT sum(single_price * sale_num) as sale_total FROM erp2_goods_sale_log WHERE goods_id={$goods_id};";
                $res = Db::query($sql)[0]['sale_total'];
                $sale_total_money = $res ? $res : 0;
                // 商品库存
                $goods['goods_sku'] = db('goods_sku')->where(['goods_id'=>$goods_id])->value('sku_num');

                $goods['sto_total_num'] = $sto_total_num;
                $goods['sto_total_money'] = $sto_total_money;
                $goods['sto_avg_money'] = $sto_avg_money;

                $goods['dep_total_money'] = $dep_total_money;
                $goods['dep_total_num'] = $dep_total_num;
                $goods['dep_avg_money'] = $dep_avg_money;
                $goods['sale_total_num'] = db('goods_sale_log')->where('goods_id', '=', $goods_id)->sum('sale_num');
                $goods['sale_total_money'] = $sale_total_money;
                $data[] = $goods;
                unset($goods);
            }
            $xls_name = '商品信息表';
            $xls_cell = array(
                array('goods_name', '商品名称'),
                array('cate_name','分类名称'),
                array('unit_name', '计量单位'),
                array('sto_avg_money', '平均进价'),
                array('goods_amount', '商品售价'),
                array('goods_sku', '商品库存'),
                array('sto_total_num', '入库总量'),
                array('sto_total_money', '入库总额'),
                array('dep_total_num', '出库数量'),
                array('dep_total_money', '出库总额'),
                array('sale_total_num', '销售总量'),
                array('sale_total_money', '销售总额'),
                array('remarks', '备注')
            );
            $this->exportExcel($xls_name, $xls_cell, $data);
        }catch (Exception $e)
        {
            Log::write($e->getMessage());
            $this->returnError(50000, '系统出错' . $e->getMessage());
//            $this->return_data(0, '50000', '系统出错');
        }
    }

    /*
     * 销售记录导出
     */
    public function sale_record_ept()
    {
        $goods_name = input('goods_name/s', '');
        $org_id = input('orgid/d', '');
        try {
            if (is_empty($org_id)) {
                $this->returnError(10000, $org_id);
            }
            $db = db('goods_detail')->where('org_id', '=', $org_id);
            if (!empty($goods_name)) {
                $db->where('goods_name', 'like', '%' . $goods_name . '%');
            }

            $goods_list = $db->field('goods_id, goods_name, cate_id')->select();
//            $response = [];
            $data = [];
            foreach ($goods_list as $goods) {
                $goods_id = $goods['goods_id'];
                $cate_name = db('goods_cate')->where('cate_id', '=', $goods['cate_id'])->value('cate_name');
                $sale_logs = db('goods_sale_log')->
                field('sale_id, sale_num, sale_code, sman_type, 
                sman_id, sale_obj_type, sale_obj_id, single_price, sum_payable,sale_time,
                pay_amount, pay_id, remark, manager')->where('goods_id', '=', $goods_id)->select();
                foreach ($sale_logs as $log) {
                    $sman_name = '';
                    $sale_obj_name = '';
                    if ($log['sman_type'] == 1) // 销售员
                    {
                        $sman_name = db('salesmans')->where('sm_id', '=', $log['sman_id'])->value('sm_name');
                    } elseif ($log['sman_type'] == 2)  // 老师
                    {
                        $sman_name = db('salesmans')->where('t_id', '=', $log['sman_id'])->value('t_name');
                    }
                    if ($log['sale_obj_type'] == 1) {
                        $sale_obj_name = db('students')->where('stu_id',
                            '=', $log['sale_obj_id'])->value('truename');
                    } else {
                        $sale_obj_name = '其他';
                    }

                    $manager = db('users')->where('uid', '=', $log['manager'])->value('nickname');
                    $manager = $manager ? $manager : '管理员';
                    $pay_type = db('payments')->where('pay_id', '=', $log['pay_id'])
                        ->value('payment_method');
                    $data[] = [
                        'goods_name' => $goods['goods_name'],
                        'cate_name' => $cate_name,
                        'sale_id' => $log['sale_id'],
                        'sale_num' => $log['sale_num'],
                        'sale_code' => $log['sale_code'],
                        'sman_name' => $sman_name,
                        'sman_type' => $log['sman_type'],
                        'sman_id' => $log['sman_id'],
                        'sale_time' => $log['sale_time'],
                        'sale_obj_type' => $log['sale_obj_type'],
                        'sale_obj_id' => $log['sale_obj_id'],
                        'sale_obj_name' => $sale_obj_name,
                        'manager' => $manager,
                        'pay_type' => $pay_type,
                        'pay_id' => $log['pay_id'],
                        'single_price' => $log['single_price'],
                        'sum_payable' => $log['sum_payable'],
                        'pay_amount' => $log['pay_amount'],
                        'remark' => $log['remark'],
                    ];
                }

            }
            $xls_name = "销售记录列表";
            $xls_cell = [
                array('goods_name', '商品名称'),
                array('cate_name', '分类名称'),
                array('sale_num', '销售数量'),
                array('sale_code', '销售单号'),
                array('sman_name', '销售员姓名'),
                array('sman_type', '销售员类型'),
                array('sale_obj_name', '销售对象类型'),
                array('single_price', '销售单价'),
                array('sum_payable', '应付金额'),
                array('pay_amount', '实际付款'),
                array('remark', '备注')
            ];
            $this->exportExcel($xls_name, $xls_cell, $data);
        }catch (Exception $e)
        {
            $this->returnError(10000, '导出失败');
        }
    }

    /*
     * 入库记录导出
     */
    public function sto_record_ept()
    {
        $goods_name = input('goods_name/s', '');
        $org_id = input('orgid/d', '');

        if (is_empty($goods_name, $org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        $data = [];
        $goods_list = db('goods_detail')->field('goods_id, goods_name')
            ->where('goods_name', 'like', '%' . $goods_name . '%')->select();
        try
        {
            foreach ($goods_list as $goods)
            {
                $goods_id = $goods['goods_id'];
                $g_name = $goods['goods_name'];
                $sto_logs =  db('goods_storage')->field('sto_id, sto_num, sto_single_price, sto_code, 
                    entry_time, manager')->where('goods_id', '=', $goods_id)->select();
                foreach ($sto_logs as $log)
                {
                    $manager = db('users')->where('uid', '=', $log['manager'])->value('nickname');
                    $data[] = [
                        'sto_id' => $log['sto_id'],
                        'goods_name'  => $g_name,
                        'sto_single_price'   => $log['sto_single_price'],
                        'sto_num'   => $log['sto_num'],
                        'sto_code'  => $log['sto_code'],
                        'entry_time' => date('Y/m/d', $log['entry_time']),
                        'sto_total_money' => $log['sto_num'] * $log['sto_single_price'],
                        'manager' => $manager
                    ];
                }
            }
            $xls_name = '入库记录列表';
            $xls_cell = [
                array('sto_code', '入库单号'),
                array('goods_name', '商品名称'),
                array('sto_single_price', '入库单价'),
                array('sto_num', '入库数量'),
                array('entry_time', '入库时间'),
                array('sto_total_money', '入款总额')
            ];
            $this->exportExcel($xls_name, $xls_cell, $data);
        }catch (Exception $e)
        {
            $this->returnError(50000, '服务器错误');
        }
    }

    /*
     * 出库记录导出
     */
    public function dep_record_ept()
    {
        $goods_name = input('goods_name/s', '');
        $org_id = input('orgid/d', '');
//        $limit = input('limit/d', 20);
//        $page = input('limit/d', 1);
        $goods_db = db('goods_detail')->where('org_id', '=', $org_id);
        if (!empty($goods_name))
        {
            $goods_db->where('goods_name', 'like', '%' . $goods_name . '%');
        }
        try
        {
            $goods_list = $goods_db->field('goods_id, goods_name')->select();
            $data = [];
            foreach ($goods_list as $goods)
            {
                $g_name = $goods['goods_name'];
                $g_id = $goods['goods_id'];

                $sto_logs = db('goods_deposit')->where('goods_id', '=', $g_id)->select();
                foreach ($sto_logs as $log)
                {
                    $manager = db('users')->where('uid', '=', $log['manager'])->value('nickname');
                    $data[] = [
                        'dep_id' => $log['dep_id'],
                        'goods_name' => $g_name,
                        'dep_num'   => $log['dep_num'],
                        'dep_price'  => $log['dep_price'],
                        'dep_total'  => $log['dep_num'] * $log['dep_price'],
                        'dep_time'  => date('Y/m/d', $log['dep_time']),
                        'dep_code'  => $log['dep_code'],
                        'manager'   => $manager,
                        'remark'    => $log['remark'],
                    ];
                }
            }
            $xls_name = '出库记录列表';
            $xls_cell = [
                array('dep_code', '出库单号'),
                array('goods_name', '商品名称'),
                array('dep_num', '出库数量'),
                array('dep_price', '出库单价'),
                array('dep_total', '出库总额'),
                array('dep_time', '出库时间'),
                array('remark', '出库备注')
            ];
//            $response = [
//                'per_page' => $limit,
//                'current_page' => $page,
//                'last_page' => count($data) / $limit + 1,
//                'data' => array_slice($data, ($page-1)*$limit, $limit)
//            ];
//            $this->returnData($response, '请求成功');
            $this->exportExcel($xls_name, $xls_cell, $data);
        }catch (Exception $e)
        {
            $this->returnError(50000, '请求失败');
        }
    }

    // 租赁记录导出
    public function rental_record_ept()
    {
        $status_arr = [1=>'在租', 2=>'超期', 3=>'已归还']; // 租凭状态对应状态
        $rent_type_arr = [0=>'', 1=>'日', 2=>'月', 3=>'年'];       // 租凭方式对应含义
        $rent_type_amount_arr = [0=>'', 1=>'rent_amount_day', 2=>'rent_amount_mon', 3=>'rent_amount_year'];
        $org_id = input('orgid/d', '');
        $start_time = input('start_time/d', '');
        $end_time = input('end_time/d', '');
        $key = input('key/s', '');  // 租客姓名/商品名称
        $status = input('status/d', 1); // 1 全部， 2在租， 3超期， 4已归还。
        if (empty($org_id))
        {
            $this->returnError(10000, '缺少机构ID');
        }
        try{
            // 租客
            $rent_obj_id = db('students')->
            where('truename', 'like', '%' . $key . '%')->value('stu_id');
            $goods_id = db('goods_detail')->
            where('goods_name', 'like', '%' . $key . '%')->value('goods_id');
            $table = db('goods_rental_log')->
            whereOr('rent_obj_id', '=', $rent_obj_id)->where('status', '=', $status)->whereOr('goods_id', '=', $goods_id);
            if (!empty($start_time) and !empty($end_time))
            {
                $table = $table->whereBetweenTime('create_time',  $start_time,  $end_time);
            }
            $data = [];
//            $total_margin = $table->sum('rent_margin'); // 总押金
//            $total_amount = $table->sum('rent_amount');  // 总租金
//            $total_prepaid_rent = $table->sum('prepaid_rent');  // 总预收租金
//            $data = [
//                'total_margin' => $total_margin,
//                'total_amount' => $total_amount,
//                'total_prepaid_rent' => $total_prepaid_rent,
//                'records' => array()
//            ];
            $logs = $table->select();   //
            foreach ($logs as $log) {
                $g_id = $log['goods_id'];
                $rent_id = $log['rent_id'];
                $rent_obj_type = $log['rent_onj_type'];
                $rent_obj_id = $log['rent_obj_id'];
                $goods_name = db('goods_detail')->where('goods_id', '=', $g_id)->value('goods_name');
                $rent_obj_name = '其他';
                if ($rent_obj_type == 1)  // 1是学生， 2是其他对象
                {
                    $rent_obj_name = db('students')->where('stu_id', '=', $rent_obj_id)
                        ->value('truename');
                }
                $rent_num = $log['rent_num'];
                $start_time = $log['start_time'];
                $end_time = $log['end_time'];
                $rent_type = $rent_type_arr[$log['rent_type']]; // 租借方式
                $rent_type_money = db('goods_detail')->      // 租借方式
                //对应的租金
                where('goods_id', '=', $g_id)->value($rent_type_amount_arr[$log['rent_type']]);
                $rent_amount = $log['rent_amount'];  // 租金金额
                $prepaid_rent = $log['prepaid_rent']; // 预付租金
                $status = $log['status'];
                if (time() > $log['end_time'] and $status != 3) // 超时未归还
                {
                    $status = 2;
                }
                $status_arr = [1=>'租借中', 2=>'已归还', 3=>'已超期'];
                $status = $status_arr[$status];
                $status_text = $status_arr[$status];    // 租凭状态对应文字
                $remarks = $log['remarks'];
                $data[] = [
                    'rent_id' => $rent_id,  // 租借记录id
                    'goods_name' => $goods_name,
                    'rent_code' => $log['rent_code'], // 租借单号
                    'rent_obj_name' => $rent_obj_name, // 租借对象姓名
                    'rent_obj_id'   => $rent_obj_id,    // 租借对象id
                    'rent_obj_type' => $rent_obj_type,  // 租借对象类型1学生, 其他
                    'rent_num'  => $rent_num,   // 租借数量
                    'start_time' => date('Y/m/d', $start_time),    // 租借开始时间
                    'end_time'  => date('Y/d/d', $end_time),   // 租借结束时间
                    'rent_type' => $rent_type,  // 租借类型
                    'rent_type_money' => $rent_type_money,  // 租借类型对应租金
                    'rent_amount' => $rent_amount,  // 租金
                    'prepaid_rent' => $prepaid_rent,    // 预付租金
                    'status' => $status,    // 租借状态
                    'status_text' => $status_text,
                    'remarks' => $remarks,
                    'pay_id' => $log['pay_id'], // 支付方式
                ];
            }
            $xls_name = "租赁记录列表";
            $xls_cell = [
                array('goods_name', '商品名称'),
                array('rent_code', '租赁单号'),
                array('rent_obj_name', '租赁对象姓名'),
                array('rent_num', '租赁数量'),
                array('start_time', '租赁开始时间'),
                array('end_time', '租赁结束时间'),
                array('rent_amount', '租金'),
                array('prepaid_rent', '预付租金'),
                array('status', '租赁状态'),
                array('remarks', '租赁备注')
            ];
            $this->exportExcel($xls_name, $xls_cell, $data);
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统错误');
        }
    }


    /*
     * 销售统计表导出
     * */
    public function sale_census_ept()
    {

        $cate_id = input('cate_id/d', ''); // 分类id
        $org_id = input('orgid/d', ''); // 机构id
        $sman_type = input('sman_type/d', ''); // 销售员类型, 1销售员, 2 老师
        $time_type = input('time_type/d', ''); // 1日/2月/3年
        $goods_name = input('goods_name/s', '');  // 商品名称
        $start_time = input('start_time/d', ''); // 开始时间
        $end_time = input('end_time/d', ''); // 结束时间
//        $page = input('page/d', 1);
//        $limit = input('limit/d', 20);
        if (is_empty($org_id))
        {
            $this->returnError(10000, '缺少参数');
        }
        try{
            $goods_db = db('goods_detail')->where('org_id', '=', $org_id);
            if (!empty($goods_name))
            {
                $goods_db->where('goods_name', 'like', '%' . $goods_name . '%');
            }
            if ($cate_id)
            {
                $goods_db->where('cate_id', '=', $cate_id);
            }
            $goods_list = $goods_db->field('goods_id, cate_id, unit_name')->select();
            $data = [];
            foreach ($goods_list as $goods)
            {
                $goods_id =  $goods['goods_id'];
                $goods_name = $goods['goods_name'];
                $unit_name = $goods['unit_name'];
//                $cate_name = db('goods_cate')->where('cate_id', '=', $cate_id)->value('cate_name');
                $sale_db = db('goods_sale_log')->where('goods_id', '=', $goods_id);
                if (!$sman_type)
                {
                    $sale_db->where('sman_type', '=', $sman_type);
                }
                if (!empty($start_time) and !empty($end_time))
                {
                    $sale_db->whereBetweenTime('sale_time', $start_time, $end_time);
                }elseif ($time_type)
                {
                    if ($time_type == 1) {$sale_db->whereTime('sale_time', 'd');}
                    elseif ($time_type == 2) {$sale_db->whereTime('sale_time', 'm');}
                    elseif ($time_type == 3) {$sale_db->whereTime('sale_time', 'y');}
                }
                // 销售总额
                $sale_total_money = $sale_db->sum('pay_amount');
                // 销售数量
                $sale_num = $sale_db->sum('sale_num');
                // 入库总额
                $sto_total_money = db('goods_storage')->where('goods_id', '=', $goods_id)
                    ->sum('sto_num*sto_single_price');
                // 入库数量
                $sto_num = db('goods_storage')->where('goods_id', '=', $goods_id)
                    ->sum('sto_num');
                // 入库平均单价
                $sto_single_price = $sto_total_money / $sto_num;
                // 销售利润
                $sale_profit = $sale_total_money - $sto_single_price * $sale_num;

                $data[] = [
                    'goods_name' => $goods_name,
//                    'cate_name' => $cate_name,
                    'unit_name' => $unit_name,
//                    '$sale_total' => $sale_total_money,
                    'sto_num'  => $sto_num,
                    'sto_total'  => $sto_total_money,

                    'sale_num' => $sale_num,
                    'sale_total' => $sale_total_money,

                    'sale_profit' => $sale_profit
                ];
            }
            $xls_name  = "销售统计表";
            $xls_cell = array(
                array('goods_name', '商品名称'),
                array('unit_name','单位名称'),
                array('sto_num', '入库数量'),
                array('sto_total', '入库总额'),
                array('sale_num', '销售数量'),
                array('sale_total', '销售总额'),
                array('sale_profit', '销售利润'),
            );
            $this->exportExcel($xls_name, $xls_cell, $data);
//            $response = [
//                'current_page' => $page,
//                'per_page' => $limit,
//                'last_page' => (count($data) / $limit) +1,
//                'total' => count($data),
//                'data' => array_slice($data, ($page-1)*$limit, $limit)
//            ];
//            $this->returnData($response, '请求成功');
        }catch (Exception $e)
        {
            $this->returnError(50000, '系统异常' . $e->getMessage());
        }
    }

}
