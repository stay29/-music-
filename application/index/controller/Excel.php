<?php

namespace app\index\controller;

use app\index\model\Classroom as ClsModel;
use think\Controller;
use think\Exception;
use think\Request;

class ExcelBase extends Controller
{
    public function returnError($error_code, $error_msg)
    {
        $data = [
            'status' => 0,
            'error_code' => $error_code,
            'error_msg' => $error_msg,
            'data' => ''
        ];
        echo json_encode($data);
    }
    public function returnData($info, $data)
    {
        $data = [
            'status' => 1,
            'sinfo' => $info,
            'error_code' => '',
            'data' => $data
        ];
        echo json_encode($data);
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
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
        ob_end_clean();
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}


class Excel extends ExcelBase
{
    public function room_tpl()
    {
        $org_id = input('orgid');
        $xlsName  = "classroom";
        $xlsCell  = array(
            array('name', '教室名称'),
            array('count','容纳人数'),
            array('status','状态'),
        );
        if (empty($org_id))
        {
            $this->returnError('10000', '缺少参数');
        }
        $data = [
            ['name'=>'教室1', 'count'=>100, 'status'=>'可用']
        ];
        $this->exportExcel($xlsName,$xlsCell,$data);
    }

    // 教室导入
    public function room_import(){
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        try
        {
            $file = request()->file('excel');
            //将文件保存到public/uploads目录下面
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
            //获取总列数
            $col_num = $sheet->getHighestColumn();
            $data = []; //数组形式获取表格数据
            for ($i=2; $i <=$row_num; $i++) {
                $data['room_name'] = $sheet->getCell("A".$i)->getValue();
                $data['room_count']     = $excel->getActiveSheet()->getCell("B".$i)->getValue();
                $data['status']     = $excel->getActiveSheet()->getCell("C".$i)->getValue();
                //将数据保存到数据库
                ClsModel::create($data)->save();
            }
            $this->returnData('导入成功', $data);
        }catch (Exception $e)
        {
            $this->returnError('50000', '导入失败');
        }
    }

    // 教室导出
    public function room_export(){
        $org_id = input('orgid');
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
     * 教师模板下载
     */
    public function teacher_tpl()
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
            array('entry_time', '入职日期'),
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
    public function teacher_ept(){
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

    public function teacher_ipt(){
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        try
        {
            $file = request()->file('excel');
            //将文件保存到public/uploads目录下面
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
}
