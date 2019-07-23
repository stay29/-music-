<?php
/*
 * teacher management;
 */

namespace app\admin\controller;

use think\Controller;

class Teacher extends Controller
{
    /*
     * show teacher list.
     */
    public function index()
    {
        $teacher_list = db('teacher')->field('id');
    }

    /*
     * edit teacher's info.
     */
    public function edit()
    {
        //
    }

    /*
     * delete teacher info.
     */
    public function del()
    {
        //
    }
}
