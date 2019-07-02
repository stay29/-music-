<?php
namespace app\index\controller;


use think\Controller;
use think\Db;

class Index extends Controller
{
   public function initialize(){

   }
   protected $beforeActionList = [
     'first',
     'second'=>['except'=>'hello'],
     'three' => ['only'=>'hello,data']
   ];
   public function first(){
       echo 'first';
   }
   public function second(){
       echo 'second';
   }
   public function three(){
       echo 'three';
   }
   public function hello(){
       return 'hello';
   }
   public function data(){


   }
}
