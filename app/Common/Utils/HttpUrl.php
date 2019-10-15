<?php
namespace App\Common\Utils;

class HttpUrl
{
    protected static $handle;

    /**
     * Get url
     *
     * @param string|array $url
     * @param array $option
     *
     */
    public static function get($url, array $option = null)
    {
        $data = self::getOption($url, $option);
        $data['method'] = 'GET';
        return self::request($data);
    }

    /**
     * Post url
     *
     * @param string|array $url
     * @param array $option
     *
     */
    public static function post($url, array $option = null)
    {
        $data = self::getOption($url, $option);
        $data['method'] = 'POST';
        return self::request($data);
    }

    /**
     * Put url
     *
     * @param string|array $url
     * @param array $option
     *
     */
    public static function put($url, array $option = null)
    {
        $data = self::getOption($url, $option);
        $data['method'] = 'PUT';
        return self::request($data);
    }

    /**
     * Delete url
     *
     * @param string|array $url
     * @param array $option
     *
     */
    public static function delete($url, array $option = null)
    {
        $data = self::getOption($url, $option);
        $data['method'] = 'DELETE';
        return self::request($data);
    }

    /**
     * 验证参数是否为数组
     *
     * @param string|array $url
     * @param array $option
     *
     */
    public static function getOption($url, array $option = null)
    {
        if (!$option) {
            $option = [];
        }

        if (is_string($url)) {
            $option['url'] = $url;
        } else if (is_array($url)) {
            $option = array_merge($url, $option);
        }

        return $option;
    }

    /**
     * 设置默认值
     *
     * @param array $data
     *
     */
    public static function processOption($data)
    {
        // 设置头信息
        $data['headers'] = !empty($data['headers']) ? $data['headers'] : ['User-Agent: DDUrl'];
        // 传输的数据类型
        $data['dataType'] = !empty($data['dataType']) ? $data['dataType'] : null;
        // 传输方式
        $data['method'] = !empty($data['method']) ? $data['method'] : 'GET';
        // 自定义
        $data['curlOpt'] = !empty($data['curlOpt']) ? $data['curlOpt'] : false;
        // 返回参数
        $data['responseType'] = !empty($data['responseType']) ? $data['responseType'] : null;
        // 安全校验
        $data['secure'] = !empty($data['secure']) ? $data['secure'] : false;
        // 超时设置
        $data['timeout'] = !empty($data['timeout']) ? $data['timeout'] : 10;
        //设置COOKIE
        $data['cookies'] = !empty($data['cookies']) ? $data['cookies'] : [];
        // 判断是否有文件需要上传
        if (isset($data['file'])) {
            foreach ($data['file'] as $fileKey => $fileVal) {
                //根据文件路径获取文件名称
                $fileName = end(explode("/", $fileVal));
                //获取文件的类型
                $fileType = mime_content_type($fileVal);
                $data['data']['files' . $fileKey + 1] = curl_file_create($fileVal, $fileType, $fileName);
            }
        }
        return $data;
    }

    /**
     * Get handle
     */
    protected static function getHandle() {
        if (!self::$handle) {
            self::$handle = curl_init();
        }

        curl_reset(self::$handle);
        curl_setopt(self::$handle, CURLOPT_HEADER, true);
        curl_setopt(self::$handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(self::$handle, CURLOPT_FORBID_REUSE, false);

        return self::$handle;
    }

    /**
     * Request url
     *
     * @param array $option
     *   file           上传文件 (支持多文件和单文件)
     *     key         上传的文件字段名称
     *     path        需上传的文件路径
     *     type        上传文件类型
     *     name        上传文件名称
     *   url            访问地址
     *   dataType       判断传输的数据类型
     *   headers        http头信息
     *   method         传输方式
     *   timeout        超时时间
     *   curlOpt        自定义设置
     *   responseType   返回类型
     *
     */
    public static function request($option)
    {
        $option = self::processOption($option);

        $handle = self::getHandle();
        curl_setopt($handle, CURLOPT_URL, $option['url']);

        if (isset($option['data']) && ($option['method'] == 'POST' || $option['method'] == 'PUT')) {
            // 判断传输的数据类型
            if ($option['dataType'] == 'json') {
                $data = json_encode($option['data'], JSON_UNESCAPED_UNICODE);
                $option['headers']['Content-Type'] = 'application/json';
            } elseif (is_array($option['data'])) {
                $data = http_build_query($option['data']);
            } else {
                $data = (string) $option['data'];
            }

            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        }

        // 设置http头信息
        if ($option['headers']) {
            $headers = [];
            foreach ($option['headers'] as $key => $val) {
                if (is_numeric($key)) {
                    $headers[] = $val;
                } else {
                    $headers[] = "$key: $val";
                }

            }
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $option['method']);

        // 超时时间
        curl_setopt($handle, CURLOPT_TIMEOUT, $option['timeout']);

        // Cookies 设置
        if ($option['cookies']) {
            $cookies = '';
            if (is_array($option['cookies'])) {
                $cookies_pairs = [];
                foreach ($option['cookies'] as $key => $val) {
                    $cookies_pairs[] = $key . '=' . $val;
                }
                $cookies = implode(';', $cookies_pairs);
            } else if (is_string($option['cookies'])) {
                $cookies = $option['cookies'];
            }

            if ($cookies) {
                curl_setopt($handle, CURLOPT_COOKIESESSION, true);
                curl_setopt($handle, CURLOPT_COOKIE, $cookies);
            }
        }

        // 安全
        if ($option['secure']) {
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $option['secure']);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $option['secure']);
        }

        // 自定义设置
        if ($option['curlOpt']) {
            curl_setopt_array($handle, $option['curlOpt']);
        }

        $res = curl_exec($handle);
        $error = curl_error($handle);

        if ($error) {
            return ['error' => $error];
        }

        //获取头信息
        $resHeaders = explode("\r\n\r\n", $res, 3);
        $header = $resHeaders[0];
        $headerLength = strlen($header);
        if (strpos($header, '100 Continue') !== false) {
            $header = $resHeaders[1];
            $headerLength += strlen($header) + 4;
        }
        $body = substr($res, $headerLength + 4);
        $headerLines = array_filter(explode("\r\n", $header));
        $headers = [];
        foreach ($headerLines as $key => $val) {
            $val = explode(": ", $val);
            (count($val) > 1) ? $headers[strtolower($val[0])] = $val[1] : '';
        }

        //获取会话信息
        $curlInfo = curl_getinfo($handle);
        // curl_close($handle);

        $returnData = [
            'error' => $error,
            'curlInfo' => $curlInfo,
            'statusCode' => $curlInfo['http_code'],
            'headers' => $headers,
            'body' => ($option['responseType'] == 'json') ? json_decode($body, true) : $body,
        ];

        return $returnData;
    }
}
