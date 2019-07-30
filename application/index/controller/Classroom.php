<?php
/**
 * 教室
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */
namespace app\index\controller;

use app\index\model\Classroom as ClsModel;
use think\Controller;
use PHPExcel;
use think\Exception;



class Classroom extends Controller
{
    /**
     * 获取教室列表
     */
    public function index()
    {
        $oid = ret_session_name('orgid');
        if(empty($oid))
        {
            $this->return_data(1, '', '', array());
        }
        $limit = input('limit/d', 20);
        $status = input('status/d', null);
        $room_name = input('name/s', null);

        $where[] = ['or_id', '=', $oid];

        if(isset($status) and ($status==2 || $status==1) and !empty($status))
        {
            $where[] = ['status', '=', $status];
        }
        if(!empty($room_name))
        {
            $where[] = ['room_name', 'like', '%' . $room_name. '%'];
        }
        $res = ClsModel::where($where)->field('room_id as id,room_name as 
            name,status,room_count as total')->paginate($limit);
        $this->return_data(1, 0, '', $res);
    }

    /**
     * 添加教室
     */
    public function add(){
        $oid = ret_session_name('orgid');
        $data = [
            'room_name' => input('post.name'),
            'status' => input('post.status'),
            'room_count' => input('post.total'),
            'oid' => $oid
        ];

        $validate = new \app\index\validate\Classroom();

        if(!$validate->scene('add')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        try{
            ClsModel::create($data)->save();
            $this->return_data(1,0,'教室新增成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    /**
     * 修改教室
     */
    public function edit(){
        $oid = ret_session_name('orgid');
        $data = [
            'room_id'=>input('post.id'),
            'room_name' => input('post.name'),
            'status' => input('post.status'),
            'room_count' => input('post.total'),
        ];
        $validate = new \app\index\validate\Classroom();
        if(!$validate->scene('edit')->check($data)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
        }
        $where = [
            'or_id' => $oid,
            'room_id' => $data['room_id']
        ];
        try{
            ClsModel::where($where)->update($data);
            $this->return_data(1,0,'教室编辑成功');
        }catch (\Exception $e){
            $this->return_data(0,50000,$e->getMessage());
        }
    }

    /**
     * 删除教室
     */
    public function del(){
        $id = input('id/d');
        $oid = ret_session_name('orgid');

        if(empty($id)){
            $this->return_data(0,10000,'缺少教室主键');
        }

        $where[] = ['room_id', '=', $id];
        $where[] = ['or_id', '=', $oid];

        try
        {
            ClsModel::where($where)->delete();
            $this->return_data(1,0,'删除教室成功');
        }catch (Exception $e){
            $this->return_data(0,20003,'删除失败');
        }
    }

    public function template()
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
            $this->return_data(0, '10000', '缺少参数');
        }
        $data = [
            ['name'=>'教室1', 'count'=>100, 'status'=>'可用']
        ];
        $this->exportExcel($xlsName,$xlsCell,$data);
    }

    public function import(){
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
                // $res = Db::name('user1')->insert($data);
            }
            $this->return_data(1, '', '导入成功');
        }catch (Exception $e)
        {
            $this->return_data(0, 50000, '服务器错误');
        }
    }


    // 教室导出
    public function export(){
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
            $this->return_data(0, '10000', '缺少参数');
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
