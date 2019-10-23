<?php

namespace app\api\controller;

use think\Controller;

class BaseController extends Controller{
    
    protected function initialize() {
        parent::initialize();
        
        //验证第三方sessionid
        self::check();
    }
    
    private function check() {
        
    }
    
    
}
