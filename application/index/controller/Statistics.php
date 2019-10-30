<?php
/**
 * 基础控制器
 * User: antony
 * Date: 2019/7/10
 * Time: 15:03
 */
namespace app\index\controller;
use think\Controller;
use think\Exception;
class Statistics extends Controller{
   
    const JSON_SUCCESS_STATUS = 1;
    const JSON_ERROR_STATUS = 0;
    /*
     * 基类初始化
     * @throws BaseException
     */
    public function _initialize()
    {

    }
    /*缺少参数提醒*/
     public  function  parameter(){
        if (!$user_id = $this->request->param('user_id')) {
            throw new Exception(['msg' => '缺少必要的参数：********']);
         }
        return $user_id;
     }
    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
     protected function renderJson($code = self::JSON_SUCCESS_STATUS, $msg = '', $data = [])
      {
        return json_encode(compact('code', 'msg', 'data'));
      }

    /**
     * 返回操作成功json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderSuccess($data = [], $msg = 'success')
    {
        return $this->renderJson(self::JSON_SUCCESS_STATUS, $msg, $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderError($msg = 'error', $data = [])
    {
        return $this->renderJson(self::JSON_ERROR_STATUS, $msg, $data);
    }





}

