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
		  	],
		  	'subject|课程科目'=>[
		  		'require',
                'number',
		  	],
		  	'describe|课程描述'=>[
		  		'require',
		  		'min'=>1,
		  		'max'=>50,
		  	],
		  	'remarks|备注'=>[
		  		'require',
		  		'min'=>1,
		  		'max'=>100,
		  	],
		  	'ctime|课时'=>[
		  		'require',
		  		'min'=>1,
                'number',
		  	],
		  ];
}
?>