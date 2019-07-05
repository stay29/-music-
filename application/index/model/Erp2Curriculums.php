<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Erp2Curriculums extends Model
{	
	protected $autoWriteTimestamp = true;
	//设置支持自动时间戳功能的字段名
  	protected $createTime = 'create_time';
  	//protected $updateTime = 'update_time';
  	//创建验证规则
	//以属性的方式进行配置,属性不能更改
	//protected $rule = [
		// 'name'=>'require|min:3|max:21',
		// 'sex' => 'in:0,1',
		// 'age' => 'require|between:14,80',
		// 'salary' => 'require|gt: 2500'
	//];
	//错误信息可以自定义: 
	//protected $message = [
		// 'name.require' => '员工姓名不能为空',
		// 'name.min' => '姓名不能少于3个字符',
		// 'name.max' => '姓名不能大于21个字符',
		// 'sex.in' => '性别只能选择男或女',
		// 'age.require' => '年龄必须输入',
		// 'age.between' => '年龄必须在14到80周岁之间',
		// 'salary.require' => '工资必须输入',
		// 'salary.gt' => '工资必须大于2500元'
	//];


	public function addc($data){
		$user  = new Erp2Curriculums;

		$user->save();
		return $user->id;
	}
}