<?php

namespace app\index\controller;
use app\index\model\Classroom as ClsModel;
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
        $str = "./public/uploads/file/classroom.xlsx";
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
        $org_id = input('org_id', '');
        $se_id = input('se_id', '');
        $status = input('status', '');
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数org_id');
        }
        $xlsName  = "教师信息";
        $xlsCell  = array(
            array('t_name', '姓名(必填)'),
            array('t_sex','性别(只能填男或者女)'),
            array('t_sen', '资历(暂时无需填写，导入后进入系统修改)'),
            array('t_mobile', '手机号(此项必填)'),
            array('t_entry_time', '入职日期(格式必须为：1970.01.01)'),
            array('t_id_card', '身份证(必填)'),
            array('t_birthday', '生日(格式为:1970.01.01)'),
            array('t_resume', '简历(非必填,最多2000字)')
        );
        $sql = "SELECT A.t_name as t_name, 
            case when A.sex = 1 then '男' when  A.sex = 2 then '女' end AS t_sex, 
            B.seniority_name as t_sen, A.cellphone as t_mobile, FROM_UNIXTIME(A.entry_time, '%Y.%m.%d') as t_entry_time,
            A.identity_card as t_id_card, FROM_UNIXTIME(A.birthday, '%Y.%m.%d') AS t_birthday,
            A.resume AS t_resume FROM erp2_teachers AS A 
            INNER JOIN erp2_seniorities AS B ON A.se_id=B.seniority_id
            WHERE org_id={$org_id}";
        if (!empty($se_id))
        {
            $sql .= " AND A.se_id={$se_id}";
        }
        if(!empty($status))
        {
            $sql .= " AND A.status={$status}";
        }
        try{
            $xlsData = Db::query($sql);
            $this->exportExcel($xlsName,$xlsCell,$xlsData);
        }catch (\Exception $e){
            $this->returnError('50000', '服务器错误');
        }
    }

    /**
     * Teacher information introduction method
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function teacher_ipt(){
        $org_id = input('org_id', '');
        $uid = input('uid', '');
        $file = request()->file('excel');
        if (empty($org_id) || empty($file))
        {
            $this->returnError('10000', '缺少文件或者org_id');
        }
        $data = $this->getExcelData($file);
        Db::startTrans();
        try{
            foreach ($data as $k => $v)
            {
                $t['t_name'] = $v[0];
                $t['sex'] = $v[1] == '男' ? 1 : 2;
                $t['se_id'] = 1;
                $t['cellphone'] = $v[3];
                $t['entry_time'] = strtotime($v[4]);
                $t['identity_card'] = trim($v[5]);
                $t['birthday'] = strtotime($v[6]);
                $t['resume'] = $v[7];
                $t['manager'] = $uid;
                $t['org_id'] = $org_id;
                Db::table('erp2_teachers')->insert($data);
                unset($t);
            }
            Db::commit();
            $this->returnData('导入成功', true);
        }catch (\Exception $e){
            Db::rollback();
            $this->returnError('导入失败', false);
        }
    }

    /**
     * Template Download for student information import
     */
    public function stu_tpl()
    {
        $str = "./public/uploads/file/students.xlsx";
        $this->returnData('', $str);
    }

    /*
     * Student Information Exporting Method
     */
    public function stu_ept()
    {
        $org_id = input('org_id', '');
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数orgid');
        }
        $xlsName  = "学生模板";

        $xlsCell  = array(
            array('stu_name', '学生姓名(必填)'),
            array('stu_sex', '性别(必填，男或女)'),
            array('stu_birthday', '出生日期(如:1996.12.31)'),
            array('stu_mobile', '手机号(必填)'),
            array('stu_wechat', '微信号(非必填)'),
            array('stu_address', '住址(非必填)'),
            array('stu_remark', ' 备注(非必填)'),
            array('stu_status', '学生状态')
        );
        $sql = "SELECT truename AS stu_name, 
                CASE WHEN sex = 1 THEN '男' WHEN sex = 2 THEN '女' END AS stu_sex,
                FROM_UNIXTIME(birthday, \"%Y.%m.%d\") AS stu_birthday,
                cellphone AS stu_mobile, wechat AS stu_wechat, address AS stu_adress,
                remark AS stu_remark FROM erp2_students WHERE org_id={$org_id}";
        try{
            $data = Db::query($sql);
            $this->exportExcel($xlsName, $xlsCell, $data);
        }catch (\Exception $e)
        {
            $this->returnError('50000', '导出失败');
        }
    }

    /*
     * Student information introduction method
     */
    public function stu_ipt()
    {
        $org_id = input('org_id', '');
        $uid = input('uid', '');
        $file = request()->file('excel');
        if(empty($org_id) || $file)
        {
            $this->returnError(10000, '缺少参数orgid或excel文件');
        }
        $data = $this->getExcelData($file);
        try{
            foreach ($data as $k=>$v)
            {
                $t['manager'] = $uid;
                $t['org_id'] = $org_id;
                $t['truename'] = $v[0];
                $t['sex'] = $v[1] == '男' ? 1 : 2;
                $t['birthday'] = strtotime($v[2]);
                $t['cellphone'] = $v[3];
                $t['wechat'] = $v[4];
                $t['address'] = $v[5];
                $t['remark'] = $v[6];
                Db::table('erp2_students')->insert($t);
                unset($t);
            }
        }catch (\Exception $e){
            $this->returnError('50000', '插入失败');
        }

    }
    /**
     * Download Data Template for Course Purchase
     */
    public function course_tpl()
    {
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数orgid');
        }
    }

    /**
     * Importing Purchasing Course Data from EXCEL
     */
    public function course_ipt()
    {

    }

    /**
     * Data of students'purchasing lessons are exported to EXCEL
     */
    public function course_ept()
    {

    }

}
