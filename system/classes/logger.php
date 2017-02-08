<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * 日志处理类（新）
 * @package Netap
 * @category System
 *
 */
class Netap_Logger
{
    public static $LOG_LEVEL = array(
        /* 跟踪  */
        'TRACE' => 0,
        /* 调试  */
        'DEBUG' => 1,
        /* 信息  */
        'INFO' => 2,
        /* 警告  */
        'WARN' => 3,
        /* 错误  */
        'ERROR' => 4,
        /* 致命错误  */
        'FATAL' => 5,
        /* 不记录  */
        'OFF' => 99
    );

    public static $LOG_TYPE = array(
        /* 发送电子邮件  */
        'EMAIL' => 1,
        /* 本地文件  */
        'FILE' => 3
    );

    /**
     * 记录跟踪信息
     * @param string $message
     */
    public static function trace($message)
    {
        if (isset(self::$LOG_LEVEL[LOG_LEVEL]) && self::$LOG_LEVEL[LOG_LEVEL] <= self::$LOG_LEVEL['TRACE']) {
            self::writeLog($message, 'TRACE');
        }
    }

    /**
     *
     * 记录调试信息
     * @param string $message
     */
    public static function debug($message)
    {
        if (isset(self::$LOG_LEVEL[LOG_LEVEL]) && self::$LOG_LEVEL[LOG_LEVEL] <= self::$LOG_LEVEL['DEBUG']) {
            self::writeLog($message, 'DEBUG');
        }
    }

    /**
     * 记录一般信息
     * @param string $message
     */
    public static function info($message)
    {
        if (isset(self::$LOG_LEVEL[LOG_LEVEL]) && self::$LOG_LEVEL[LOG_LEVEL] <= self::$LOG_LEVEL['INFO']) {
            self::writeLog($message, 'INFO');
        }
    }

    /**
     * 记录警告信息
     * @param string $message
     */
    public static function warn($message)
    {
        if (isset(self::$LOG_LEVEL[LOG_LEVEL]) && self::$LOG_LEVEL[LOG_LEVEL] <= self::$LOG_LEVEL['WARN']) {
            self::writeLog($message, 'WARN');
        }
    }

    /**
     * 记录错误信息
     * @param string $message
     */
    public static function error($message)
    {
        if (isset(self::$LOG_LEVEL[LOG_LEVEL]) && self::$LOG_LEVEL[LOG_LEVEL] <= self::$LOG_LEVEL['ERROR']) {
            self::writeLog($message, 'ERROR');
        }
    }

    /**
     *
     * 记录致命错误信息
     * @param string $message
     */
    public static function fatal($message)
    {
        if (isset(self::$LOG_LEVEL[LOG_LEVEL]) && self::$LOG_LEVEL[LOG_LEVEL] <= self::$LOG_LEVEL['FATAL']) {
            self::writeLog($message, 'FATAL');
        }
    }

    /**
     * 写日志文件
     * @param string $message记录内容
     * @param string $level制定错误级别
     * @param string $file指定文件名
     * @param string $line指定行号
     * @param string $logfile指定日志文件名
     */
    public static function write_log($message, $level = '', $file = '', $line = '', $logfile = '')
    {
        //解决debug_backtrace() 无法获取行号的问题
        self::writeLog($message, $level, $file, $line, $logfile);
    }

    /**
     *
     * @param string $message
     * @param string $logfile
     * @param number $showprefix
     */
    public static function write_diylog($message, $logfile, $showprefix = 0)
    {
        self::writeLog($message, '', '', '', $logfile, $showprefix);
    }

    /**
     * 根据日志级别配置，写日志文件
     * @param string $message
     * @param string $level
     * @param string $file
     * @param string $line
     * @param string $logfile
     * @param number $showprefix 是否显示前缀信息 1：显示 0：不显示
     */
    private static function writeLog($message, $level = '', $file = '', $line = '', $logfile = '', $showprefix = 1)
    {
        if (isset(self::$LOG_TYPE[LOG_TYPE])) {
            list ($usec, $sec) = explode(' ', microtime());
            $logtime = date('Y-m-d H:i:s:' . floor($usec * 1000), $sec);
            if ($showprefix == 1) {
                if (empty($file)) {
                    $trace = debug_backtrace();
                    if (isset($trace) && isset($trace[1])) {
                        $file = isset($trace[1]['file']) ? $trace[1]['file'] : 'FILE';
                        $line = isset($trace[1]['line']) ? $trace[1]['line'] : '';
                    }
                }
                $prefix = '[' . $logtime . '][' . $level . '][' . self::get_request_uri() . '][' . $file . '][LINE' . $line . ']' . PHP_EOL;
                $message = $prefix . $message . PHP_EOL;
            } else {
                $message = $message . '[' . $logtime . ']' . PHP_EOL;
            }

            if (self::$LOG_TYPE[LOG_TYPE] == self::$LOG_TYPE['EMAIL']) {
                error_log($message, self::$LOG_TYPE[LOG_TYPE], LOG_EMAIL);
            } elseif (self::$LOG_TYPE[LOG_TYPE] == self::$LOG_TYPE['FILE']) {
                $writefile = LOG_PATH . DIRECTORY_SEPARATOR . (empty($logfile) ? (date('y_m_d') . '.log') : $logfile);
                error_log($message, self::$LOG_TYPE[LOG_TYPE], $writefile);
            }
        }
    }

    /**
     * 获取请求的路径
     */
    private static function get_request_uri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '-';
    }
}
