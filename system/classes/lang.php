<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * 语言包读取类
 * @package Netap
 * @category System
 * @author OAM Team
 *
 */
class Netap_Lang
{
    /** 存放合并后的语言包变量 */
    private static $vals = array();

    /** 存放语言包名 */
    private static $paths = array();

    /**
     * 加载语言包
     *
     * @example Netap_Lang::load('user/lang_passport');
     * @param $path
     * @internal param string $name
     */
    public static function load($path)
    {
        $lang = array();
        $path = APPPATH . '/language/' . LANGUAGE . '/' . $path . '.php';
        if (array_key_exists($path, self::$paths)) {
            return;
        }

        include $path;
        self::$paths[$path] = TRUE;
        //self::$vals = array_merge( self::$vals, $lang );
        self::$vals = $lang + self::$vals;
    }

    /**
     * 获取语言包变量
     *
     * @example
     * array('password_len_limit' => '密码长度必须在:min到:max个字符')
     * Netap_Lang::val('email_format_error',array(':min' => 3, ':max'=>6));
     *
     * @param string $k 键
     * @param array $param 参数
     * @return string
     */
    public static function val($k, array $param = NULL)
    {
        if (isset(self::$vals[$k])) {
            if (is_array($param)) {
                return strtr(self::$vals[$k], $param);
            }
            return self::$vals[$k];
        } else {
            return '{' . $k . '}';
        }
    }

    /**
     * 获取语言包变量，可不用调用load方法一次性使用
     * @example Netap_Lang::get('user/lang_passport', 'email_format_error',array(':min' => 3, ':max'=>6));
     * @param string $path 语言包路径
     * @param string $k 键
     * @param array $param 参数
     * @return Ambigous <string, multitype:>
     */
    public static function get($path, $k, array $param = NULL)
    {
        self::load($path);
        return self::val($k, $param);
    }
}
