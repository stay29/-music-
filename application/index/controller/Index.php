<?php
namespace app\index\controller;
use think\Controller;
use PHPExcel;
class Index extends controller
{
    public function index()
    {
        return view();
    }
    //到出模板接口
    public function product()
    {   
        $excel = new \PHPExcel();
        //2.指定当前的sheet
        $objSheet = $excel->setActiveSheetIndex(0);
        //写入值
        $objSheet->setCellValue('A1', '姓名');
        $objSheet->setCellValue('B1', '分数');
        //设置F列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(30);
        $list =        
        for($i=0;$i<count($list);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$list[$i]['id']);//添加ID
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$list[$i]['name']);//添加姓名
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$list[$i]['age']);//添加年龄
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$list[$i]['class']);//添加班级
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$list[$i]['tel']);//添加电话
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$list[$i]['email']);//添加邮箱
        }

        //4.保存文件
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        //告诉浏览器以xlsx文件输出
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //Content-Disposition 消息头指示输出内容该以何种形式展示， attachment：消息体应该被下载到本地
        //filename的值预填为下载后的文件名
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        //设置缓存存储的最大周期，超过这个时间缓存被认为过期(单位秒),
        //max-age=0 即不缓存
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }




}