<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * 核心处理程序类
 * @package Netap
 * @category System
 *
 */
class Netap
{

    /**
     * @var  string  character set of input and output
     */
    public static $charset = 'utf-8';

    /** 初始化程序  */
    public static function init()
    {
        /* 设定错误处理方法  */
        set_error_handler(array('Netap', 'app_error'), E_ALL);

        /* 设定异常处理方法  */
        set_exception_handler(array('Netap', 'app_exception'));

        /* 设定动态加载类  */
        spl_autoload_register(array('Netap', 'auto_load'));

        /* 记录系统严重错误  */
        register_shutdown_function(array('Netap', 'shutdown'));
    }

    /**
     *
     * 自动加载类处理方法
     * @param 类名 $class
     *
     * @return null
     */
    public static function auto_load($class)
    {
        if (stripos($class, 'libraries\\') === 0) {
            $parts = explode('\\', $class);
            $classname = array_pop($parts);
            set_include_path(DOCROOT . implode(DIRECTORY_SEPARATOR, $parts));
            spl_autoload($classname, '.php');
            return;
        }

        if (!stripos($class, '_')) {
            return null;
        }

        list ($prefix, $classname) = explode('_', $class, 2);
        $path = '';
        switch ($prefix) {
            case 'Netap' :
                $path = SYSPATH . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
                break;
            case 'Controller' :
                $path = Netap_Request::$controller_path;
                if (substr($classname, 0, 4) == 'Abs_') {
                    $path .= PATH_SEPARATOR . APPPATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
                }
                break;
            case 'Cache' :
                $path = APPPATH . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
                break;
            case 'Model' :
                $path = APPPATH . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
                break;
            case 'Service' :
                $path = APPPATH . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR;
                break;
            case 'Helper' :
                $path = APPPATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . SYSPATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
                break;
            case 'Module' :
                $path = MODPATH . DIRECTORY_SEPARATOR . strtolower($classname) . DIRECTORY_SEPARATOR;
                break;
            default :
                return null;
        }

        set_include_path($path);
        spl_autoload($classname, '.php');
    }

    /**
     * 退出系统时调用，用于发生严重错误时记录日志
     */
    public static function shutdown()
    {
        $lasterror = error_get_last();
        if (is_array($lasterror)) {
            self::app_error($lasterror['type'], $lasterror['message'], $lasterror['file'], $lasterror['line']);
        }
    }

    /**
     * 统一错误处理类
     * @param 错误代码 $errcode
     * @param $errmsg
     * @param 出错文件 $errfile
     * @param 出错文件行数 $errline
     * @internal param 错误描述 $errstr
     */
    public static function app_error($errcode, $errmsg, $errfile, $errline)
    {

        $loglevel = 'ERROR';
        switch ($errcode) {
            case E_WARNING :
            case E_USER_WARNING :
                $loglevel = 'WARN';
                break;
            case E_NOTICE :
            case E_USER_NOTICE :
                $loglevel = 'INFO';
                break;
            default :
                break;
        }

        //$errmsg = iconv( 'GBK', 'UTF-8', $errmsg );

        /*  写入日志文件 */
        Netap_Logger::write_log($errmsg, $loglevel, $errfile, $errline);

        /*  根据错误级别来判断是否显示错误信息 */
        if (($errcode & error_reporting()) === 0) {
            return;
        }

        /*  显示错误信息 */
        if (IS_DEBUG) {
            self::write_debug_error($errcode, $errmsg, $errfile, $errline, debug_backtrace());
        } else {
            self::write_normal_error($errcode, $errmsg);
        }
    }

    /**
     *
     * 统一异常处理类
     * @param 错误异常 $e
     */
    public static function app_exception($e)
    {
        $loglevel = 'ERROR';

        //$errmsg = iconv( 'GBK', 'UTF-8', $e->getMessage() );
        $errmsg = $e->getMessage();
        $errcode = $e->getCode();
        $errfile = $e->getFile();
        $errline = $e->getLine();

        switch ($errcode) {
            case E_WARNING :
            case E_USER_WARNING :
                $loglevel = 'WARN';
                break;
            case E_NOTICE :
            case E_USER_NOTICE :
                $loglevel = 'INFO';
                break;
            default :
                break;
        }

        /*  写入日志文件 */
        Netap_Logger::write_log($errmsg, $loglevel, $errfile, $errline);

        if ($e instanceof Netap_NotFoundException) {
            self::write_notfound_error();
            return;
        }

        /*  显示错误信息 */
        if (IS_DEBUG) {
            self::write_debug_error($errcode, $errmsg, $errfile, $errline, $e->getTrace());
        } else {
            self::write_normal_error($errcode, $errmsg);
        }
    }

    public static function write_notfound_error()
    {
        if (PHP_SAPI === 'cli') {
            echo '404 Not Found';
            exit();
        }

        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header('HTTP/1.1 404 Not Found');
        @require SYSPATH . '/errors/error_404.php';
        exit();
    }

    /**
     *
     * 回写一般错误到屏幕
     * @param 错误代码 $errcode
     * @param 错误信息 $errmsg
     */
    public static function write_normal_error($errcode, $errmsg)
    {
        $e = array();
        $e['errcode'] = $errcode;
        $e['errmsg'] = $errmsg;
        if (PHP_SAPI === 'cli') {
            $e['date'] = date('y-m-d H:i:m');
            print_r($e);
            exit();
        }

        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header('HTTP/1.1 500 Internal Server Error');
        @require SYSPATH . '/errors/error_normal.php';
        exit();
    }

    /**
     *
     * 回写调试错误到屏幕
     * @param 错误编码 $errcode
     * @param 错误消息 $errmsg
     * @param 文件名 $errfile
     * @param 行数 $errline
     * @param 堆栈信息 $trace
     */
    public static function write_debug_error($errcode, $errmsg, $errfile, $errline, $trace)
    {
        $e = array();
        $e['errcode'] = $errcode;
        $e['errmsg'] = $errmsg;
        $e['file'] = $errfile;
        $e['line'] = $errline;
        $traceInfo = '';
        $time = date('y-m-d H:i:m');
        foreach ($trace as $t) {
            $traceInfo .= '[' . $time . '] ';
            if (isset($t['file'])) {
                $traceInfo .= $t['file'];
            }
            if (isset($t['line'])) {
                $traceInfo .= ' (' . $t['line'] . ') ';
            }
            if (isset($t['class'])) {
                $traceInfo .= $t['class'];
            }
            if (isset($t['type'])) {
                $traceInfo .= $t['type'];
            }
            $traceInfo .= $t['function'] . '(';
            if (isset($t['args'])) {
                $args_arr = array();
                foreach ($t['args'] as $key => $args) {
                    if (!is_string($args)) {
                        unset($t['args'][$key]);
                    }

                    /* 参数暂时隐藏不显示出来 */
                    $args_arr[] = gettype($args) . '(' . sizeof($args) . ')';
                }
                $traceInfo .= implode(', ', $args_arr);
            }
            $traceInfo .= ')' . "\n";
        }
        $e['trace'] = $traceInfo;
        if (PHP_SAPI === 'cli') {
            print_r($e);
            exit();
        }

        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header('HTTP/1.1 500 Internal Server Error');
        @require SYSPATH . '/errors/error_debug.php';
        exit();
    }
}
