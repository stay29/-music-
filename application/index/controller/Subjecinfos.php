<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/16
 * Time: 14:40
 */
namespace app\index\controller;
use think\Controller;
use app\index\model\Subjects;


class Subjecinfos  extends BaseController
{
    public function index()
    {
        $res = Subjects::getall();
        $this->return_data(1, 0, $res);
    }

    public function get_subjects()
    {
        $data['sid'] = input('sid');
        $res = Subjects::get_noe_subjects($data);
        $this->return_data(1, 0, $res);
    }
}