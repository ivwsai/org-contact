<?php defined('SYSPATH') or die('No direct script access.');

/**
 * HTTP helper.
 *
 * @package    Netap
 * @category   Helpers
 * @author     OAM Team
 */
class Helper_Http
{
    private static $httpCodeArray = Array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Checkpoint',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Resume Incomplete',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict', //对当前资源状态，请求不能完成
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        420 => 'Method Failure',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        426 => 'Upgrade Required',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        0 => 'Unknow',
    );

    private static $httpMimeArray = Array(
        'JS' => 'application/x-javascript',
        'JSON' => 'application/json',
        'RAR' => 'application/x-rar-compressed',
        'PDF' => 'application/pdf',
        'XML' => 'text/xml',
        'XHTML' => 'application/xhtml+xml',
        'HTML' => 'text/html',
        'TXT' => 'text/plain',
        'WORD' => 'application/msword',
        'UNKNOW' => 'application/octet-stream',
        'PLIST' => 'application/x-plist',
        /* 待补充 */
    );


    /**
     * 获取HTTP状态码对应描述
     *
     * 0**：未被始化
     * 1**：请求收到，继续处理
     * 2**：操作成功收到，分析、接受
     * 3**：完成此请求必须进一步处理
     * 4**：请求包含一个错误语法或不能完成
     * 5**：服务器执行一个完全有效请求失败
     * @param int $code HTTP状态码
     * @return string 状态码对应描述内容
     */
    public static function codeInfo($code)
    {
        return (isset(self::$httpCodeArray[$code])) ? self::$httpCodeArray[$code] : self::$httpCodeArray[0];
    }


    /**
     * 获取Mime信息描述
     *
     * @param string $name Mime名称
     * @return string 返回Mime对应信息描述
     */
    public static function mimeInfo($name)
    {
        return (isset(self::$httpMimeArray[$name])) ? self::$httpMimeArray[$name] : self::$httpMimeArray['UNKNOW'];
    }

    /**
     * 发送JSON响应到浏览器， $httpcode请固定使用200
     *
     * @param int $httpcode ,HTTP状态码
     * @param array /string $jsonBody,应答内容数组格式，如果文本则返回200
     * @param string $mime ,Mime类型，参考self::$httpMimeArray定义
     */
    public static function writeJson($httpcode = 200, $jsonBody = '', $mime = '')
    {
        /* 之前有缓存输出的话，清空 */
        if (ob_get_length() > 0) {
            ob_end_clean();
        }

        if ($mime == '') {
            if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
                $mime = 'TXT';
            } else {
                $mime = 'JSON';
            }
        }

        header('HTTP/1.1 ' . $httpcode . ' ' . self::codeInfo($httpcode));
        header('Content-type: ' . self::mimeInfo($mime));
        if (!is_array($jsonBody)) {
            $jsonBody = array('code' => $httpcode, 'msg' => $jsonBody);
        }
        echo json_encode($jsonBody);
        exit ();
    }

    /**
     *
     * 发送HTML格式的响应
     *
     * @param string $html_body
     * @param string $only_body
     * @param string $charset
     */
    public function writeHtml($html_body, $only_body = true, $charset = 'utf-8')
    {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header('HTTP/1.1 200 OK');
        header('Content-type: text/html; charset=' . $charset);
        if (!$only_body) {
            echo '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=' . $charset . '"></head><body>';
            echo $html_body;
            echo '</body></html>';
        } else {
            echo $html_body;
        }
        exit ();
    }

    /**
     * 跳转到指定URL
     * @example Helper_Http::redirect('http://test.91.com');
     * @param string $url
     * @param number $code
     */
    public static function redirect($url, $code = 302)
    {
        if ($code < 300 || $code >= 400) {
            throw new Netap_Exception('错误的转向代码：' . $code);
        }

        $msg = self::$httpCodeArray[0];
        if (isset (self::$httpCodeArray [$code])) {
            $msg = self::$httpCodeArray [$code];
        }

        header('HTTP/1.1 ' . $code . ' ' . $msg);
        header("Location: $url");
        exit ();
    }

    /**
     * 去除转义反斜线
     * @param string /array $data
     * @return mixed
     */
    public static function stripSlashes($data)
    {
        if (is_array($data)) {
            $new_data = array();
            foreach ($data as $key => $val) {
                $new_data[$key] = self::stripSlashes($val);
            }
            return $new_data;
        }

        return stripslashes($data);
    }

    /**
     * 判断当前请求是否POST方式
     */
    public static function isPostRequest()
    {
        return (self::getRequestMethod() == 'POST') ? true : false;
    }

    /**
     * 判断当前请求是否GET方式
     */
    public static function isGetRequest()
    {
        return (self::getRequestMethod() == 'GET') ? true : false;
    }

    public static function getRequestMethod()
    {
        return isset ($_SERVER ['REQUEST_METHOD']) ? trim($_SERVER ['REQUEST_METHOD']) : '';
    }

    /**
     * 判断当前请求是否Ajax
     */
    public static function isAjaxHeader()
    {
        return (isset ($_SERVER ['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER ['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    /**
     * 判断当前请求Content-Type是否javascript/json方式
     */
    public static function isJsonContent()
    {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $contenttype = explode(';', $_SERVER['CONTENT_TYPE']);
            if (isset($contenttype[0])) {
                return strtolower(trim($contenttype[0])) == self::mimeInfo('JSON');
            }
        }
        return false;
    }

    /**
     * 检测链接是否是SSL连接
     * @return boolean
     */
    public static function isSsl()
    {
        if (isset ($_SERVER ['HTTPS']) && (($_SERVER ['HTTPS'] === 1) || ($_SERVER ['HTTPS'] === 'on'))) {
            return TRUE;
        }

        if (isset ($_SERVER ['SERVER_PORT']) && $_SERVER ['SERVER_PORT'] == 443) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取客户端IP地址
     * @return string
     */
    public static function getClientIp()
    {
        if (isset($_SERVER["HTTP_X_REAL_IP"])) {
            $onlineip = $_SERVER["HTTP_X_REAL_IP"];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $onlineip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        } else {
            $onlineip = '';
        }

        return $onlineip;
    }

    /**
     * 获取服务器IP地址
     * @return string
     */
    public static function getServerIp()
    {
        $server_ip = null;
        if (isset($_SERVER['SERVER_ADDR'])) {
            $server_ip = $_SERVER ['SERVER_ADDR'];
        } else if (getenv('SERVER_ADDR')) {
            $server_ip = getenv('SERVER_ADDR');
        } else if (isset($_SERVER['LOCAL_ADDR'])) {
            $server_ip = $_SERVER ['LOCAL_ADDR'];
        }

        return $server_ip;
    }
}
