<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Url helper.
 *
 * @package Netap
 * @category Helpers
 */
class Helper_Url
{

    /**
     * 如果当前主机和$host不匹配，则将当前地址跳转到$host给定的主机地址
     *
     * @example redirectHost('www.91.com');
     * @param string $host
     */
    public static function redirectHost($host)
    {
        $cur_host = $_SERVER ['HTTP_HOST'];
        $request_uri = isset ($_SERVER ['REQUEST_URI']) ? $_SERVER ['REQUEST_URI'] : '';
        if ($cur_host !== $host) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: http://' . $host . $request_uri);
        }
    }

    /**
     * 获取当前页面地址
     *
     * @return string
     */
    public static function currentUrl()
    {
        $url = 'http';
        $domain = isset ($_SERVER ['HTTP_HOST']) ? $_SERVER ['HTTP_HOST'] : $_SERVER ['SERVER_NAME'];
        if (isset ($_SERVER ['HTTPS']) && $_SERVER ["HTTPS"] == "on") {
            $url .= "s";
        }
        $url .= "://";
        if ($_SERVER ["SERVER_PORT"] != "80") {
            $url .= $domain . ':' . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];
        } else {
            $url .= $domain . $_SERVER ["REQUEST_URI"];
        }
        return $url;
    }

    /**
     * URL特殊字符处理
     *
     * @param stirng $value
     * @param boolean $double_encode
     */
    public static function urlSpecialchars($value, $double_encode = TRUE)
    {
        return htmlspecialchars(( string )$value, ENT_QUOTES, 'utf-8', $double_encode);
    }

}
