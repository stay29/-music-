<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
namespace think;
// echo 333;
// exit;
// 加载基础文件
require __DIR__ . '/thinkphp/base.php';
$_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI' ];
define('UPLOAD_DIR', __DIR__ . '/public/upload'.DIRECTORY_SEPARATOR);
define('MA','erp2');
define('MB','Piano_');
//定义余额支付id，根据数据库id,后续只要定义一个常量数组就好
define('BALANCE_PAY', 6);
Container::get('app',[__DIR__ . '/application/'])->run()->send();

