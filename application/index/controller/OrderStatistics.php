<?php
namespace app\index\controller;
use  app\common\model\Purchase_Lessons as PurchaseLessons; 
use  app\common\model\Lessons as Lessons; 
use  app\common\model\Balance as Balance;
use  app\common\model\Expend as Expend;
use  app\common\model\TeachSchedules as TeachSchedules;
use  app\common\model\Students;
use  app\common\model\Users;
use  app\common\model\Csres;
use  app\common\model\Classes;
use  app\common\model\Payments;
use  think\DB;
use think\facade\Request;
/**
 * 统计板块之一
 * @package app\index\controller
 */
class OrderStatistics extends Statistics
{ 
  /*定义每个页面出现的数目*/
  protected $num;
  public  function   __construct(){
    $this->num = 15;
  }
  //做完就构造
  //条件返回空没设置
   /* 订单统计
        erp2_purchase_lessons_record
        "r_id": 1,   //订单号
        "give_class": "0", //赠送的课时
        "class_hour": "1", //购买课时
        "disc_price": "0.00", //优惠价格
        "real_price": "12.00", //实付价格
        "buy_time": "2019-09-09 16:10", //购买时间
        "student_name": null,  //学生名字
        "users_name": "校长182", //操作人
        "pay_name": "支付宝",   //支付方式
        "cur_name": "啊实打实大aaaaaaa" //课程
          'single_price'      //单价=>原价/课时
         'type'     //折扣
          no                 //跟进人
                            //支付状态
             //应付款（）
     */
  public  function  index(){ 
    // try() 关联erp2_purchase_lessons
     try{     
    $where =array();
    $wheretime =array();
    $data= Request::post()? Request::post():array();
    // 判断where值
    if(isset($data['cur_id'])) $where['cur_id'] =$data['cur_id']; //课程
    if(isset($data['pay_id'])) $where['pay_id'] =$data['pay_id']; //付款方式
    if(isset($data['sub_id'])) $where['sub_id'] =$data['sub_id']; //课程
    // 时间
    if(isset($data['starttime']) && isset($data['endtime'])){
       $wheretime =array($data['starttime'],$data['endtime']);
      }else{
        $wheretime =array('1970-1-1',date('Y-m-d'));
      }
      // return json($wheretime);
    $page = Request::get('page')?Request::get('page'):'1';
    $num =$this->num;
    $startpage = $num*($page-1);
    $count_all  = PurchaseLessons::where($where)->whereTime('buy_time',$wheretime)->count('r_id');
    $num = $count_all <= $num ?$count_all:$num;
    $record =   PurchaseLessons::field('r_id,give_class,class_hour,disc_price,real_price,buy_time,classify,meal_id,cur_id,stu_id,manager,pay_id,type,original_price,type_num')
              ->where($where)
              ->whereTime('buy_time',$wheretime)
              ->with(['student','users','pay'])
              ->limit($startpage,$num)
              ->order('r_id', 'desc')
              ->select();
              // ->toArray();
      foreach ($record as $key => $value) {
        if($value['type'] != 2){
           $record[$key]['type'] ='100'.'%';
        }else{
          $record[$key]['type'] = $record[$key]['type_num'];
        }
          $record[$key]['student_name'] = $value['student']['truename'];
          $record[$key]['users_name'] = $value['users']['nickname'];
          $record[$key]['pay_name'] = $value['pay']['payment_method'];
          $sum_class =(int)$value['give_class']+(int)$value['class_hour'];
          if( $sum_class != 0){
             $record[$key]['single_price'] =   $value['real_price'] == '0' ? '0':$value['real_price']/$sum_class;
          }else{
             $record[$key]['single_price'] = '0';
          }
          if($value['classify'] == '1'){
          $record[$key]['cur_name']=Db::name('curriculums')->field('cur_name')->where('cur_id',$value['cur_id'])->find()['cur_name'];
          }else{
          $record[$key]['cur_name'] =DB::name('meals')->field('meal_name')->where("meal_id",$value['meal_id'])->find()['meal_name'];   
          }
        unset($record[$key]['student'],$record[$key]['users'],$record[$key]['classify'],$record[$key]['cur_id'],$record[$key]['meal_id'],$record[$key]['stu_id'],$record[$key]['manager'],$record[$key]['pay_id'],$record[$key]['pay'],$record[$key]["original_price"],$record[$key]["type_num"]);
      }
      $record['count'] =$count_all;
       //返回总数 ,和数据 //差个总金额
      return json($record);
    // return  $record;
    } catch (\Exception $e) {
      $code = '500';
      $error = '参数报错';
      return json_encode(compact("code","error"));
    }

  }
  /*欠费统计
      stu_name         学生名字
      cur_name         课程名字
      teacher_name     上课老师  
      sign_price       收费情况 /课时
      暂时没有         欠费金额
      count_class      购买课时
      surplus_hour    循环课时
      alreay_class     已上课
      alreay_pai      已经排课
      real_price      收费金额
       */
  public function  arrears(){
   // erp2_purchase_lessons
     try{     
    $where =array();
    $data= Request::post()? Request::post():array();
    $page = Request::get('page')?Request::get('page'):'1';
    // 判断where值
    //退学不退学找不出来
     // $student_where  = $data['name'] ? array('truename' => array()):array();
    if(isset($data['name'])){
     $students_data  = DB::name('students')->field('group_concat(stu_id) as  stu_id')->where('truename','like','%'.$data['name'].'%')->select();
     $post_id =  $students_data[0]['stu_id'];
     if($post_id){
      $where[]=array('stu_id','in',$post_id);
     }
     }
    $startpage = 15*($page-1);
    $num =$this->num;
    $count_all  = Lessons::where($where)->group('cur_id,stu_id')->count('r_id');
    $num = $count_all <= $num ?$count_all:$num;

    // $sql = 'select * from erp2_purchase_lessons group by '
    // $record  = Db::query($sql);
    $record =   Lessons::field('stu_id,cur_id,sum(ifnull(class_hour,0))+sum(ifnull(give_class,0)) as count_class,sum(surplus_hour) as surplus_hour,group_concat(t_id) as t_id,sum(real_price) as real_price')
              ->where($where)
              ->limit($startpage,$num)
              ->order('id', 'desc')
              ->group('cur_id,stu_id')
              ->select();
foreach ($record as $key => $value) {
  $record[$key]['stu_name'] = DB::name('students')->field('truename')->where('stu_id',$value['stu_id'])->find()['truename']; //学生名
  $record[$key]['cur_name'] = DB::name('curriculums')->field('cur_name')->where('cur_id',$value['cur_id'])->find()['cur_name']; //课程名
  //教师名字
  $teacher  = array_unique(explode(',',$value['t_id']));
  $t_data= DB::name('teachers')->field('group_concat(t_name) as  t_names')->where('t_id','in',$teacher)->select(); 
  $record[$key]['teacher_name'] =$t_data[0]["t_names"];
  //以上 和以排的 区别
  $condition['cur_id'] = $value['cur_id'];
  $condition['stu_id'] = $value['stu_id'];
   $record[$key]['alreay_pai']= DB::name('tschedules_history')->where($condition)->count('th_id');
   $class_sum = 0;
  // erp2_teach_schedules 相关表
  $history =  DB::name('tschedules_history')->field('th_id')->where($condition)->select();
   foreach ($history as $k => $v) {
    $res_condition['stu_id'] = $value['stu_id'];
    $res_condition['th_id']=$v['th_id'];
    $res_condition['status']=2;
    $res = DB::name('teach_schedules')->field('sc_id')->where($res_condition)->find();
     if($res){$class_sum++;}
   }
   $record[$key]['alreay_class']= $class_sum;
    unset($record[$key]['t_id']);
   }
   //后期加数组
      return  $record;
   } catch (\Exception $e) {
      $code = '500';
      $error = '参数报错';
      return json_encode(compact("code","error"));
    }
       
  }
  /* erp2_stu_balance 学生余额表
     yu_money  账户余额
     total_recharge  累计赠送
     total_gift 累计充值
     buy_money   累计消费
     stu_name //学生姓名
  */
  public function account(){
     try{    
    $where =array();
    $data= Request::post()? Request::post():array();
    $page = Request::get('page')?Request::get('page'):'1';
    // // 判断where值
    // //退学不退学找不出来
    if(isset($data['name'])){
     $students_data  = DB::name('students')->field('group_concat(stu_id) as  stu_id')->where('truename','like','%'.$data['name'].'%')->select();
     $post_id =  $students_data[0]['stu_id'];
     if($post_id){
      $where[]=array('stu_id','in',$post_id);
     }
     }
     $num =$this->num;
     $startpage =  $num*($page-1);
     $count_all  = Balance::where($where)->count('stu_id');
     $num = $count_all <= $num ?$count_all:$num;
     $record =   Balance::field('stu_id,ifnull(gift_balance,0)+ifnull(recharge_balance,0) as yu_money,total_recharge,total_gift,total_buylesson+total_shop as buy_money')
              ->where($where)
              ->limit($startpage,$num)
              ->order('stu_id', 'desc')
              ->select();
    foreach ($record as $key => $value) {
    $record[$key]['stu_name'] = DB::name('students')->field('truename')->where('stu_id',$value['stu_id'])->find()['truename']; //学生名
    unset($record[$key]['stu_id']);
   }
        return json($record);
    } catch (\Exception $e) {
      $code = '500';
      $error = '参数报错';
      return json_encode(compact("code","error"));
    }
    //end
  }
  
  /* 费用统计
  erp2_expend_log
  *
  */
  public  function expend(){
    try{
     $where =array();
    $data= Request::post()? Request::post():array();
    $page = Request::get('page')?Request::get('page'):'1';
    //  判断  条件
   if(isset($data['name'])) $where[]=array('name','like','%'.$data['name'].'%'); //名字
   if(isset($data['type_id'])) $where[]=array('type_id','eq',$data['type_id']);//支出类型
   //时间类型 和时间的所有判断
    if(isset($data['starttime']) && isset($data['endtime']) && isset($data['timetype'])){
      if($data['timetype'] == 'paytime'){
        $where[] = array('pay_time', 'between time', array($data['starttime'], $data['endtime']));
      }else{
        $where[] = array('mark_time', 'between time', array($data['starttime'], $data['endtime']));
      }
      }
    
     $num =$this->num;
     $startpage =  $num*($page-1);
     $count_all  = Expend::where($where)->count('id');
     $num = $count_all <= $num ?$count_all:$num;
     $record =   Expend::field('id,name,mark_time,pay_time,amount,manager,remark,type_id,org_id')
              ->where($where)
              ->limit($startpage,$num)
              ->order('id', 'desc')
              ->select();
foreach ($record as $key => $value) {
  $wexpend['id'] =     $value['type_id'];
  $wexpend['org_id'] =     $value['org_id'];
  $record[$key]['type_name'] = DB::name('expend_type')->field('type_name')->where($wexpend)->find()['type_name'];
  $wuser['uid']  =  $value['manager'];
  $record[$key]['nickname']=DB::name('users')->field('nickname')->where($wuser)->find()['nickname'];
  unset($record[$key]['org_id'],$record[$key]['manager'],$record[$key]['type_id']);
  }
    return json($record);
      } catch (\Exception $e) {
      $code = '500';
      $error = '参数报错';
      return json_encode(compact("code","error"));
    }
  }
  /*教师-学生课时统计
   *erp2_teach_schedules erp2_purchase_lessons
  */
  public function scclass(){
   $where =array();
   $whereor =array();
    $data= Request::post()? Request::post():array();
    $page = Request::get('page')?Request::get('page'):'1';
    //  判断  
    //搜索姓名
    if(isset($data['name'])){
    $t_where[]=array('t_name','like','%'.$data['name'].'%');
     $stu_where[]=array('truename','like','%'.$data['name'].'%');
     $t_res = DB::name('teachers')->field('group_concat(t_id) as t_id ')->where($t_where)->select();
     $stu_res = DB::name('students')->field('group_concat(stu_id) as stu_id')->where($stu_where)->select();
     if($t_res) $where[]=array('t_id','in',$t_res[0]['t_id']);
     if($stu_res) $whereor[]=array('stu_id','in',$stu_res[0]['stu_id']);
    }
    //搜索时间
     if(isset($data['starttime']) && isset($data['endtime'])){
        $where[] = array('end_time', 'between time', array($data['starttime'], $data['endtime']));
      }
   //选择班级(没有开发)
     $num =$this->num;
     $startpage =  $num*($page-1);
     $count_all  = TeachSchedules::where($where)->group('cur_id,stu_id,buy_id')->count('sc_id');
     $num = $count_all <= $num ?$count_all:$num;
     $record =   TeachSchedules::field("cur_id,type,count(cur_id) as cur_num,stu_id,cur_id,sum(if(status=1,'1','0')) as noclass,sum(if(status=2,'1','0')) as realyclass,t_id,cost,buy_id")
              ->where($where)
              ->whereor($whereor)
              ->limit($startpage,$num)
              ->order('sc_id', 'desc')
              ->group('cur_id,stu_id,buy_id')
              ->select(); 
    foreach ($record as $key => $value) {
    $record[$key]['stu_name'] = DB::name('students')->field('truename')->where('stu_id',$value['stu_id'])->find()['truename']; //学生名
    $record[$key]['cur_name'] = DB::name('curriculums')->field('cur_name')->where('cur_id',$value['cur_id'])->find()['cur_name']; //课程名
    $record[$key]['ter_name'] = DB::name('teachers')->field('t_name')->where('t_id',$value['t_id'])->find()['t_name'];
    unset($record[$key]['cur_id'],$record[$key]['stu_id'],$record[$key]['t_id']);
    }
      return json($record);
  }
  //课程薪酬
  //直播统计 =》直播订单、直播课时、直播结算
  //班级统计 //没开始  

  public  function  class(){
    //order  limit;
    //erp2_classes erp2_class_student_relations  erp2_student
    // $class =  DB::name('classes')->where($where)->limit(20,10)->select();
    // foreach ($class as $key => $value) {
       
    // }
    // details();
    // dump($class);
    // exit;
    //  $where = array();
    //  $page = 3;
    //  $count = 15*($page-1);
    // $record =   Classes::with(['details'])
    //           ->where($where)
    //           ->limit($count,15)
    //           ->select();
    //           dump($record);
  }

}
