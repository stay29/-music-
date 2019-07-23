<?php
namespace app\index\controller;
use think\Controller;
use PHPExcel;
use think\Db;
use think\facade\Session;
use app\index\model\Curriculums;
class Index extends BaseController
{
    public function index()
    {
        $userinfo =Session::get(md5(MA.'user'));
        $arr = Session::get($userinfo['id']);
        print_r($arr);exit();
        return view();
    }

    //导入
    public  function  daochu(){
        $kname = ['cur_name', 'subject', 'tmethods', 'ctime', 'describe', 'remarks'];
        $res = $this->import($kname);
       foreach ($res as $k=>$v){
           $info =  Curriculums::create($v);
       }
       if($info){
           $this->return_data(1,0,'导入成功');
       }else{
           $this->return_data(0,50000,'导入失败');
       }
    }

    //到出
    public  function  daoru(){
    $kname = array(
            array('cur_name','课程名称'),
            array('subject','科目分类'),
            array('tmethods','授课方式'),
            array('ctime','课时'),
            array('describe','备注'),
            array('remarks','描述'),
        );
    $list =  db('curriculums')->select();
    $this->export('课程列表',$kname,$list);
    }



    //导出excil
    public  function  currm_export(){
        $kname = array(
            array('cur_name','课程名称'),
            array('subject','科目分类'),
            array('tmethods','授课方式'),
            array('ctime','课时'),
            array('describe','备注'),
            array('remarks','描述'),
        );
        $cur_name = input('cur_name');
        $subject = input('subject');
        $tmethods = input('tmethods');
        $status = input('status');
        $orgid  = session(md5(MA.'user'))['orgid'];
        $where = null;
        if($cur_name){
            $where[]=['cur_name','like','%'.$cur_name.'%'];
        }
        if($subject){
            $where[]=['subject','=',$subject];
        }
        if($tmethods){
            $where[]=['tmethods','=',$tmethods];
        }
        if($status){
            $where[]=['status','=', $status];
        }
        if($orgid){
            $where[]=['orgid','=', $orgid];
        }
        $list =  db('curriculums')->where($where)->select();
        $this->export('课程列表',$kname,$list);
    }


    //公共导入方法返回数组
    public function import($kname)
    {
        //获取表单上传文件
        $file = request()->file('excel');
        $info = $file->validate(['ext' => 'xlsx,xls'])->move('./upload/file/');
        //数据为空返回错误
        if(empty($info)){
            $output['status'] = false;
            $output['info'] = '导入数据失败~';
            $this->ajaxReturn($output);
        }
        //获取文件名
        $exclePath = $info->getSaveName();
        //上传文件的地址
        $filename = './upload/file/'. $exclePath;
        //判断截取文件
        $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );
        //区分上传文件格式
        if($extension == 'xlsx') {
            $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }else if($extension == 'xls'){
            $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }
        $excel_array = $objPHPExcel->getsheet(0)->toArray();   //转换为数组格式
         //array_shift($excel_array);
       // $res = array_serch($kname,$excel_array);
        foreach ($excel_array as $k=>$v){
            unset($excel_array[0]);
            unset($excel_array[1]);
        }
        $fils = array_serch($kname,$excel_array);
        return $fils;
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
//        $expCellName  = array(
//            array('cur_name','商品id'),
//            array('subject','上下架'),
//            array('tmethods','商品名称'),
//            array('ctime','品牌名称'),
//            array('describe','分类名称'),
//            array('remarks','供应商'),
//        );
        //$expTableData  = db('curriculums')->select();
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
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


}