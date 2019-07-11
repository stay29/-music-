<?php
namespace app\index\controller;
use think\Controller;
use PHPExcel;
use app\index\model\Curriculums;
class Index extends controller
{
    public function index()
    {   

        return view();
    }


    public function export($filename,$data,$dataname){
        //1.从数据库中取出数据
        $list = Curriculums::getall(10); 
        //3.实例化PHPExcel类
        $objPHPExcel = new PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ID')                      
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '年龄')
                ->setCellValue('D1', '班级')
                ->setCellValue('E1', '电话')
                ->setCellValue('F1', '邮箱');
        //设置F列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(30); 
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($list);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$list[$i]['cur_id']);//添加ID
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$list[$i]['cur_name']);//添加姓名
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$list[$i]['subject']);//添加年龄
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$list[$i]['tmethods']);//添加班级
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$list[$i]['ctime']);//添加电话
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$list[$i]['describe']);//添加邮箱
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