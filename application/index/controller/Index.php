<?php
namespace app\index\controller;
use think\Controller;
use PHPExcel;
use think\Db;
use think\facade\Session;
use app\index\model\Curriculums;
use app\index\controller\Phpexcil;
use app\index\model\Meals as Mealss;
use app\index\model\MealCurRelations as Mclmodel;
class Index extends Basess
{
    protected $beforeActionList = [
        'first',
    ];

    protected function first()
    {
       new Phpexcil();
    }
    public  function  index(){
        return view();
    }

    public  function  sss(){
        $suball = db('subjects')->select();
        foreach ($suball as $kll=>&$vll){
            $subjectinfo_list[] = $vll['sname'];
        }
        $subjectinfo_list  = implode(',',$subjectinfo_list);
        $kname = array(
            array('meal_name','套餐名称'),
            array('course_model','课程模式1课次2包时'),
            array('meals_cur','课程名称'),
            array('cur_num','课程节数'),
            array('cur_value','课程价值'),
            array('actual_price','实现价值'),
            array('price','套餐价格'),
            array('cur_state','套餐状态 1启动 2不启动'),
            array('value','套餐价值'),
        );
        Phpexcil::export_tow_aaa('课程列表',$kname,array(),$subjectinfo_list);
    }

    public  function  inp_list_name()
    {
        $orgid = ret_session_name('orgid');
        $orgid = 30;
        $list = Curriculums::where('orgid',$orgid)->select();
        $arr  = array();
        foreach ($list as $k=>&$v){
            $arr[] = $v['cur_name'];
        }
        $arr = implode(',',$arr);
        return $arr;
    }
    //导出套餐模板
    public  function  setmeal_export()
    {
        $arr = $this->inp_list_name();
        $moban = input('moban');
        $kname = array(
            array('meal_name','套餐名称'),
            array('meals_cur','课程名称'),
            array('cur_num','课程节数'),
            array('cur_value','课程价值'),
            array('actual_price','实现价值'),
            array('price','套餐价格'),
            array('cur_state','套餐状态 1启动 2不启动'),
            array('course_model','课程模式1课次2包时'),
            array('value','套餐价值'),
        );
        $meal_name = input('meal_name');
        $cur_state = input('cur_state');
        $orgid  =input('orgid');
        if($meal_name){
            $where[]=['meal_name','like','%'.$meal_name.'%'];
        }
        if($cur_state){
            $where[]=['cur_state','=',$cur_state];
        }
        $where[] = ['orgid','=',$orgid];
        if($moban=='1'){
            $list = array();
            Phpexcil::export_tow_aaa('课程列表',$kname,$list,$arr);
        }else{
            $list = Mealss::getall($limit,$where);
            Phpexcil::export_tow_aaa('课程列表',$kname,$list,$arr);
        }
        }

        //套餐导入
        public  function  setmeal_Import()
        {
            $kname = ['meal_name','meals_cur', 'cur_num',  'cur_value','actual_price','price','cur_state','course_model','value'];
            $uid = input('uid');
            $orgid = input('orgid');
            $res = Phpexcil::import($kname);
            $infos = array();
            //去除空数组
            foreach ($res as $ks=>&$vs) {
                if($vs['meal_name']!=null){
                    $infos[] = $vs;
                }
            }
            //处理数据 筛选数据
            $arr1 = array();
            $arr2 = array();
            // print_r($infos);exit();
            Db::startTrans();
            try {
            foreach ($infos as $k=>&$v) {
                $arr2['actual_price'] = $v['actual_price'];
                $arr2['cur_name'] = $v['meals_cur'];
                $arr2['cur_num'] = $v['cur_num'];
                $arr2['actual_price'] = $v['actual_price'];
                $arr2['course_model'] = $v['course_model'];
                $arr2['cur_value'] = $v['cur_value'];
                $cur_id = Curriculums::where('cur_name',$v['meals_cur'])->find();
                $arr2['cur_id'] = $cur_id['cur_id'];
                $validate = new \app\validate\MealCurRelations;
                if(!$validate->scene('add')->check($arr2)) {
                    $error = explode('|',$validate->getError());
                    $this->return_data(0,$error[1],$error[0]);
                    exit();
                }else{
                    $mea_info = Mclmodel::create($arr2);
                }
                //print_r($mea_info);exit();
                $arr1['meal_name'] = $v['meal_name'];
                $arr1['value'] = $v['value'];
                $arr1['price'] = $v['price'];
                $arr1['cur_state'] = $v['cur_state'];
                $arr1['create_time'] = time();
                $arr1['orgid'] =  ret_session_name('orgid');
                $arr1['meals_cur'] = $mea_info['id'].',';
                $arr1['manager'] = ret_session_name('uid');
                $validate2 = new \app\validate\Meals;
                if(!$validate2->scene('addtow')->check($arr1)){
                    //为了可以得到错误码
                    $error2 = explode('|',$validate2->getError());
                    $this->return_data(0,$error2[1],$error2[0]);
                    exit();
                }else {
                    $mea_info2 = Mealss::create($arr1);
                }
                }
                // 提交事务
                Db::commit();
                if($mea_info2){
                    self::return_data_sta(1,0,'添加成功',$mea_info2);
                }else{
                    self::return_data_sta(1,0,'添加失败',$mea_info2);
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }


        }





    //课程导入模板
    public  function  Import_currm(){
        $kname = ['cur_name', 'subject', 'tmethods', 'ctime', 'describe', 'remarks'];
        $uid = input('uid');
        $orgid = input('orgid');
        $res = Phpexcil::import($kname);
        //print_r($res);exit();
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

        if(empty($infos)){
            $this->return_data(0,10000,'请填写数据后导入');
        }
        $validate = new \app\validate\Curriculums;
        if(!$validate->scene('add')->check($infos)){
            //为了可以得到错误码
            $error = explode('|',$validate->getError());
            $this->return_data(0,$error[1],$error[0]);
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
            array('cur_name','课程名称(必填)'),
            array('subject','科目分类(必填)'),
            array('tmethods','授课方式 1 :1对1 ,2:一对多(必填)'),
            array('ctime','课时 如 60分钟 填写60(必填)'),
            array('describe','备注(必填)'),
            array('remarks','描述(必填)'),
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
        Phpexcil::export_tow_aaa('课程列表',$kname,$list,$subjectinfo_list);
    }



}