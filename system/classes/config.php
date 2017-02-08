<?php defined('SYSPATH') or die('No direct access');

/**
 *
 *  Netap_Config 配置读取类
 * @package Netap
 * @category System
 * @author OAM Team
 *
 */
class Netap_Config
{
    /**
     *  读取配置项，需调用load方法后使用
     * @param string $name
     * @return array|string
     */
    public static function config($name, $path = '')
    {
        static $config;

        if (!isset($config[$name . $path])) {
            $config[$name . $path] = self::load($name, $path);
        }

        return $config[$name . $path];
    }

    /**
     * 加载配置文件
     * @param string $name
     * @return mixed
     */
    public static function load($name, $path = '')
    {
        $prefix = empty($path) ? APPPATH . DIRECTORY_SEPARATOR . 'config' : DOCROOT . $path;
        $file = $prefix . DIRECTORY_SEPARATOR . $name . '.php';
        return include $file;
    }
}