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

        $fileName = $xlsTitle . date('_YmdHis');

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $objPHPExcel->getActiveSheet()->setTitle("教室信息");
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
//        $org_id = input('orgid');
//        $xlsName  = "classroom";
//        $xlsCell  = array(
//            array('name', '教室名称'),
//            array('count','容纳人数'),
//            array('status','状态'),
//        );
//        if (empty($org_id))
//        {
//            $this->returnError('10000', '缺少参数');
//        }
//        $data = [
//            ['name'=>'教室1', 'count'=>100, 'status'=>'可用']
//        ];
//        $this->exportExcel($xlsName,$xlsCell,$data);
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
//        header("content-type:text/html;charset=utf-8");
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
                $data['room_count'] = $val[1];
                if(!is_numeric($data['room_count']) || !is_numeric($data['room_count']))
                {
                    $this->returnError('10001', '数据有误');
                }
                $data['status'] = $val[2];
                $data['manager'] = $uid;
                $data['or_id'] = $org_id;
//                $where[] = ['or_id', '=', $org_id];
//                $where[] = ['room_name', '=', $data['room_name']];
//                $where[] = ['is_del', '=', 0];
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
            array('id','教室ID'),
            array('name', '教室名称'),
            array('count','容纳人数'),
            array('status','状态'),
        );
        if (empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        $xlsData = db('classrooms')->where('or_id', $org_id)->field('room_id as id, 
            room_name as name, room_count as count, status')->select();
        $data = [];
        foreach ($xlsData as $k => $v)
        {
            if($v['status'] == 1)
            {
                $v['status'] = '可用';
            }
            else
            {
                $v['status'] = '不可用';
            }
            $data[] = $v;
        }
        $this->exportExcel($xlsName,$xlsCell,$data);
    }


    /*
     * Template Download for teacher information import
     */
    public function teacher_tpl()
    {
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数orgid');
        }
        $xlsName  = "教师";

        $xlsCell  = array(
            array('t_name', '教师名称'),
            array('se', '教师资历'),
            array('sex','教师性别'),
            array('identity_card', '身份证'),
            array('cellphone','电话号码'),
            array('birthday', '生日'),
            array('entry_time', '入职日期'),
            array('resume', '简历')
        );
        $sql = "SELECT A.t_name, B.seniority_name as se, A.sex, 
                A.identity_card, A.cellphone, A.birthday, A.entry_time, 
                A.resume FROM erp2_teachers AS A INNER JOIN erp2_seniorities 
                AS B ON A.se_id=B.seniority_id WHERE org_id={$org_id} LIMIT 2";

        $data = Db::query($sql);

        $this->exportExcel($xlsName,$xlsCell,$data);
    }


    // Teacher Information Exporting Method
    public function teacher_ept(){
        $org_id = input('org_id');
        $xlsName  = "教师";
        $xlsCell  = array(
            array('t_id','教师ID'),
            array('t_name', '教师姓名'),
            array('se', '教师资历'),
            array('sex','教师性别'),
            array('identity_card', '身份证'),
            array('cellphone','电话号码'),
            array('birthday', '生日'),
            array('entry_time', '入职日期'),
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

    /**
     * Teacher information introduction method
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function teacher_ipt(){
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        try
        {
            $file = request()->file('excel');
            //将文件保存到public/uploads/ecxcel
            $info = $file->validate(['size'=>1048576,'ext'=>'xls, xlsx'])->move( UPLOAD_DIR . 'excel/');
            if($info){
                //获取上传到后台的文件名
                $fileName = $info->getSaveName();
                //获取文件路径
                $filePath = UPLOAD_DIR.'excel/'. $fileName;
                //获取文件后缀
                $suffix = $info->getExtension();
                //判断哪种类型
                if($suffix=="xlsx"){
                    $reader = \PHPExcel_IOFactory::createReader('Excel2007');
                }else{
                    $reader = PHPExcel_IOFactory::createReader('Excel5');
                }
            }else{
                $this->error('文件过大或格式不正确导致上传失败-_-!');
            }
            //载入excel文件
            $excel = $reader->load("$filePath", $encode = 'utf-8');
            //读取第一张表
            $sheet = $excel->getSheet(0);
            //获取总行数
            $row_num = $sheet->getHighestRow();
//            //获取总列数
//            $col_num = $sheet->getHighestColumn();
            $data = []; //数组形式获取表格数据
            for ($i=2; $i <=$row_num; $i++) {
                $data['t_name'] = $sheet->getCell("A".$i)->getValue();
                $se_name = $sheet->getCell("B".$i)->getValue();
                $data['se_id'] = db('seniorities')->
                    where('seniority_name', 'like', "%{$se_name}%")
                    ->value('seniority_id as se_id');
                $sex_text = $excel->getActiveSheet()->getCell("C".$i)->getValue();
                if(trim($sex_text) == '男')
                {
                    $data['sex'] = 1;
                }
                else{
                    $data['sex'] = 2;
                }
                $data['identity_card'] = $excel->getActiveSheet()->getCell("D".$i)->getValue();
                $data['cellphone'] = $excel->getActiveSheet()->getCell("E".$i)->getValue();
                $data['birthday'] = $excel->getActiveSheet()->getCell("F".$i)->getValue();
                $data['entry_time'] = $excel->getActiveSheet()->getCell("G".$i)->getValue();
                $data['entry_time'] = strtotime(date("Y/m/d", $data['entry_time']));
                $data['resume'] = $excel->getActiveSheet()->getCell("H".$i)->getValue();
                //将数据保存到数据库
                ClsModel::create($data)->save();
            }
            $this->returnData('导入成功', $data);
        }catch (Exception $e)
        {
            $this->returnError('50000', '导入失败');
        }
    }

    /**
     * Template Download for student information import
     */
    public function stu_tpl()
    {
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数orgid');
        }
        $xlsName  = "学生模板";

        $xlsCell  = array(
            array('stu_name', '学生姓名'),
            array('sex', '学生性别'),
            array('birthday', '出生日期'),
            array('cellphone', '手机号码'),
            array('wechat', '微信号'),
            array('address', '地址'),
            array('remark', '备注'),
            array('entry_time', '录入时间'),
            array('status', '状态')
        );
        $sql = "SELECT truename as stu_name, 
                sex, birthday, cellphone, wechat, 
                address, remark, create_time as entry_time, 
                status, org_id FROM erp2_students 
                WHERE org_id={$org_id} LIMIT 2";
        $result = Db::query($sql);
        $data = [];
        foreach ($result as $key=>$value)
        {
            $value['entry_time'] = date('Ymd', $value['entry_time']);
            if ($value['sex'] == 1)
            {
                $value['sex'] = '男';
            }
            else
            {
                $value['sex'] = '女';
            }
            if($value['status'] == 1)
            {
                $value['status'] = '在学';
            }
            else
            {
                $value['status'] = '退学';
            }
            $data[] = $value;
        }
        $this->exportExcel($xlsName, $xlsCell, $data);
    }

    /*
     * Student Information Exporting Method
     */
    public function stu_ept()
    {
        $org_id = input('orgid', '');
        if(empty($org_id))
        {
            $this->returnError('10000', '缺少参数orgid');
        }
        $xlsName  = "学生模板";

        $xlsCell  = array(
            array('stu_id', '学生ID'),
            array('stu_name', '学生姓名'),
            array('sex', '学生性别'),
            array('birthday', '出生日期'),
            array('cellphone', '手机号码'),
            array('wechat', '微信号'),
            array('address', '地址'),
            array('remark', '备注'),
            array('entry_time', '录入时间'),
            array('status', '状态')
        );
        $sql = "SELECT stu_id, truename as stu_name, 
                sex, birthday, cellphone, wechat, 
                address, remark, create_time as entry_time, 
                status, org_id FROM erp2_students 
                WHERE org_id={$org_id} LIMIT 2";
        $result = Db::query($sql);
        $data = [];
        foreach ($result as $key=>$value)
        {
            $value['entry_time'] = date('Ymd', $value['entry_time']);
            if ($value['sex'] == 1)
            {
                $value['sex'] = '男';
            }
            else
            {
                $value['sex'] = '女';
            }
            if($value['status'] == 1)
            {
                $value['status'] = '在学';
            }
            else
            {
                $value['status'] = '退学';
            }
            $data[] = $value;
        }
        $this->exportExcel($xlsName, $xlsCell, $data);
    }

    /*
     * Student information introduction method
     */
    public function stu_ipt()
    {
        $orgid = input('orgid', '');
        $uid = input('uid', '');
        if(empty($orgid))
        {
            $this->returnError(10000, '缺少参数orgid');
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
