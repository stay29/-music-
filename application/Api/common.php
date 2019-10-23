<?php

    function httpCurl($url, $params, $method = 'GET', $header = array(), $multi = false){
        date_default_timezone_set('PRC');        
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_COOKIESESSION  => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_COOKIE  => session_name().'='.session_id(),
        );        

        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){            
        case 'GET':                
        // $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            // 链接后拼接参数  &  非？
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;            
        case 'POST':                //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
        }        
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);        
        $data  = curl_exec($ch);        
        $error = curl_error($ch);

        curl_close($ch);
        if($error) throw new Exception('请求发生错误：' . $error);        
        return  $data;
    }
