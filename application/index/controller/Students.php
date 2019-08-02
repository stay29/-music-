<?php

namespace app\index\controller;

use think\Controller;
use think\Exception;
use think\Request;
use app\index\validate\Students as StuValidate;
use app\index\model\Students as StuModel;


class Students extends BaseController
{
    /*
     * 显示学生列表
     */
    public function index()
    {
        $list_rows = input('list_rows', 10);
        $students = db('students')->field('stu_id, truename, sex, birthday,
        cellphone, wechat, address, remark')->page($list_rows);
        $this->return_data(1, '', '', $students);
    }

    /*
     * 修改学生信息
     */
    public function edit()
    {
        $data = input();
        if(!$this->request->isPost())
        {
            $this->return_data(0, '10007', '请用post方法提交数据');
        }

        $validate = new StuValidate();
        if(!$validate->scene('edit')->check($data)){
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try
        {
            $stu = StuModel::update($data);
            $stu->save();
            $this->return_data(1, '', '添加学生成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, 50000, '服务器错误', false);
        }
    }

    /*
     * 删除学生信息
     */
    public function del()
    {
        $stu_id = input('stu_id', '');
        if(empty($stu_id))
        {
            $this->return_data(0, 10000, '缺少请求参数');
        }
        try
        {
            StuModel::destroy($stu_id);
            $this->return_data(1, '', '删除成功', true);
        }catch (Exception $e){
            $this->return_data(0, '', '删除失败', false);
        }
    }

    /*
     * 增加学生信息
     */
    public function add()
    {
        if(!$this->request->isPost())
        {
            $this->return_data(0, '10007', '请用post方法提交数据');
        }
        $data = input();
        $validate = new StuValidate();

        if(!$validate->scene('add')->check($data)){
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try
        {
            $stu = StuModel::create($data);
            $stu->save();
            $this->return_data(1, '', '添加学生成功', true);
        }catch (Exception $e)
        {
            $this->return_data(0, 50000, '服务器错误', false);
        }
    }

    public function schdule()
    {

    }


    /*
     * 导入学生
     */
    public function importStu()
    {

    }

    /**
     * 导入购课信息
     */
    public function importCourse()
    {

    }


    /** 导出EXCEL文件
     * @param $expTitle
     * @param $expCellName
     * @param $expTableData
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
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
