<?php
namespace app\index\controller;
use think\Controller;
use PHPExcel;
use think\Db;
use think\facade\Session;
use app\index\model\Curriculums;
class Index extends Basess
{

    public function index()
    {

        return view();
    }

    //导入模板
    public  function  Import_currm(){
        $kname = ['cur_name', 'subject', 'tmethods', 'ctime', 'describe', 'remarks'];
        $uid = input('uid');
        $orgid = input('orgid');
        $res = $this->import($kname);
        $infos = array();
        foreach ($res as $ks=>&$vs) {
            if($vs['cur_name']!=null){
                $infos[] = $vs;
            }
        }
        //数据处理
        foreach ($infos as $k=>&$v){
            $where['sname'] = $v['subject'];
            $subinfe = db('subjects')->where($where)->find();
            if($subinfe){
                $v['subject'] = $subinfe['sid'];
            }else{
                $this->return_data(0,10000,'缺少重要参数');
            }
            $v['orgid'] = $orgid;
            $v['manager'] = $uid;
            $v['create_time'] = time();
            $v['status'] = 1;
            $v['conversion'] = 1;
            $v['state'] = 2;
            $v['popular'] = 2;
            $v['tqualific'] = '0/0';
        }
        Db::startTrans();
        try {
        foreach ($infos as $k=>$vs){
           $info =  Curriculums::create($vs);
        }
            Db::commit();
        }catch(\Exception $e){
            // 回滚事务
            Db::rollback();
            $this->return_data(0,50000,$e->getMessage());
        }
       if($info){
           $this->return_data(1,0,'导入成功');
       }else{
           $this->return_data(0,50000,'导入失败');
       }
    }

    //导出excil
    public  function  currm_export(){
        $suball = db('subjects')->select();
        foreach ($suball as $kll=>&$vll){
            $subjectinfo_list[] = $vll['sname'];
        }
        $subjectinfo_list  = implode(',',$subjectinfo_list);
        $kname = array(
            array('cur_name','课程名称'),
            array('subject','科目分类 有'.$subjectinfo_list),
            array('tmethods','授课方式 1 :1对1 ,2:一对多'),
            array('ctime','课时 如 60分钟 填写60'),
            array('describe','备注'),
            array('remarks','描述'),
        );
        $cur_name = input('cur_name');
        $subject = input('subject');
        $tmethods = input('tmethods');
        $status = input('status');
        $orgid  = input('orgid');
        $moban = input('moban');
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
        if($moban==1){
            $list = array();
        }else{
            $list =  db('curriculums')->where($where)->select();
            foreach ($list as $k=>&$v)
            {
                 $where1['sid'] = $v['subject'];
                 $subjectinfo = db('subjects')->where($where1)->find();
                 $v['subject'] = $subjectinfo['sname'];
                 if($v['tmethods']=='1'){
                    $v['tmethods'] = '1对1';
                 }else{
                    $v['tmethods'] = '一对多';
                 }
            }
        }
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
        $sheet = $objPHPExcel -> getSheet(0);
        $highestRow = $sheet -> getHighestRow(); // 取得总行数
       // print_r($objPHPExcel);exit();
        $excel_array = $sheet->toArray();//转换为数组格式
        $fils = array_serch($kname,$excel_array);
        foreach ($fils as $kl=>&$vl){
            if($kl==0 or $kl==1){
                unset($fils[$kl]);
            }
        }
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

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }
        //设置宽高
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(30);
        }
        //设置第二行内容
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][0]);
        }
        //循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<$dataNum;$i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
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

    //到出模板
    public  function  daoru_(){
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

}