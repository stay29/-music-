<?php
namespace app\validate;
use think\Validate;
class Curriculums extends Validate
{
		  protected  $rule = [
		  	'cur_name|课程名称'=>[
		  		'require',
		  		'min'=>1,
		  		'max'=>20,
		  	]
		  ];
}
?>