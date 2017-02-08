<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * URL处理帮助类
 * @package Netap
 * @category System
 * @author  OAM Team
 * @deprecated 新代码建议不要使用本类，逐步切换到Helper_Url类
 */
class Netap_URL
{

    public static function base($index = FALSE, $protocol = FALSE)
    {
        $base_url = SITEROOT;

        if ($protocol === TRUE) {
            $protocol = Netap_Request::$protocol;
        } elseif ($protocol === FALSE and $scheme = parse_url($base_url, PHP_URL_SCHEME)) {
            $protocol = $scheme;
        }

        if ($index === TRUE and !empty(Netap::$index_file)) {
            $base_url .= Netap::$index_file . '/';
        }
        if (is_string($protocol)) {
            if ($port = parse_url($base_url, PHP_URL_PORT)) {
                $port = ':' . $port;
            }

            if ($domain = parse_url($base_url, PHP_URL_HOST)) {
                $base_url = parse_url($base_url, PHP_URL_PATH);
            } else {
                $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            }

            $base_url = $protocol . '://' . $domain . $port . $base_url;
        }

        return $base_url;
    }

    public static function site($uri = '', $protocol = FALSE)
    {
        $path = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($uri, '/'));
        if (!Netap_UTF8::is_ascii($path)) {
            $path = preg_replace('~([^/]+)~e', 'rawurlencode("$1")', $path);
        }

        return Netap_URL::base(TRUE, $protocol) . $path;
    }

    public static function query(array $params = NULL, $use_get = TRUE)
    {
        if ($use_get) {
            if ($params === NULL) {
                $params = $_GET;
            } else {
                $params = array_merge($_GET, $params);
            }
        }

        if (empty($params)) {
            return '';
        }

        $query = http_build_query($params, '', '&');

        return ($query === '') ? '' : ('?' . $query);
    }

    public static function title($title, $separator = '-', $ascii_only = FALSE)
    {
        if ($ascii_only === TRUE) {
            $title = Netap_UTF8::transliterate_to_ascii($title);

            $title = preg_replace('![^' . preg_quote($separator) . 'a-z0-9\s]+!', '', strtolower($title));
        } else {
            $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', UTF8::strtolower($title));
        }

        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * 获取当前页面地址
     *
     * @return string
     */
    public static function current_url()
    {

        $pageURL = 'http';
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }

        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $domain . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $domain . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }

    /**
     * 获取当前的URI路径
     * @return string
     */
    public static function current_uri(array $params = NULL)
    {

        $uri = '/';
        if (!empty($_SERVER['REQUEST_URI'])) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        } else if (!empty($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        }

        return $uri;
    }
}
