<?php defined('SYSPATH') or die('No direct script access.');

/**
 * curl操作类
 *

 */
class Helper_Curl
{

    /**
     * Declaring content types
     *
     * @var string
     */
    public static $content_type = "application/json;charset=utf-8";

    /**
     * flag to return only the headers
     *
     * @var boolean
     */
    public static $is_headers_only = false;

    /**
     * Additional curl options to instantiate curl with
     *
     * @var array
     */
    public static $curl_options = array();

    /**
     * Additional headers to send in the request
     *
     * @var array
     */
    public static $headers = array();

    /**
     * The specified host IP
     * @var array
     */
    public static $hosts = array();

    /**
     * Execute an HTTP GET request using curl
     *
     * @param String $url to request
     * @param mixed $data
     * @throws Exception
     * @return RESTResponse
     */
    public static function get($url, $data = null)
    {
        $headers = array();
        self::setUp($url, $headers);

        if ($data) {
            if (!is_string($data)) {
                $data = http_build_query($data);
            }

            if (strpos($url, '?') === false) {
                $url = $url . '?' . $data;
            } else {
                $url = $url . '&' . $data;
            }
        }

        return Module_Request::get($url, $headers, self::$is_headers_only, self::$curl_options);
    }

    /**
     * Execute an HTTP POST request, posting the past parameters
     *
     * @param String $url to request
     * @param mixed $data to post to $url
     * @throws Exception
     * @return RESTResponse
     */
    public static function post($url, $data = null)
    {
        $headers = array();
        self::setUp($url, $headers, $data);

        return Module_Request::post($url, $data, $headers, self::$is_headers_only, self::$curl_options);
    }

    /**
     * Execute an HTTP PUT request, posting the past parameters
     *
     * @param String $url
     * @param mixed $data
     * @throws Exception
     * @return RESTResponse
     */
    public static function put($url, $data = null)
    {
        $headers = array();
        self::setUp($url, $headers, $data);

        return Module_Request::put($url, $data, $headers, self::$is_headers_only, self::$curl_options);
    }

    /**
     * Delete
     * Execute an HTTP Delete request, posting the past parameters
     *
     * @param String $url
     * @throws Exception
     * @return RESTResponse
     */
    public static function delete($url)
    {
        $headers = array();
        self::setUp($url, $headers);

        return Module_Request::delete($url, $headers, self::$is_headers_only, self::$curl_options);
    }

    /**
     * Patch
     * Execute an HTTP Patch request, posting the past parameters
     *
     * @param String $url
     * @param mixed $data
     * @throws Exception
     * @return RESTResponse
     */
    public static function patch($url, $data = null)
    {
        $headers = array();
        self::setUp($url, $headers, $data);

        return Module_Request::patch($url, $data, $headers, self::$is_headers_only, self::$curl_options);
    }

    /**
     * 预处理数据
     * @param string $url
     * @param array $headers
     * @param mixed $data
     */
    private static function setUp(&$url, &$headers, &$data = null)
    {
        $headers = array(
            "Content-Type: " . self::$content_type
        );

        //random host ip
        if (self::$hosts) {
            $scheme = parse_url($url);
            if (isset($scheme['host'])) {
                $index = mt_rand(0, count(self::$hosts) - 1);
                $host_ip = self::$hosts[$index];
                $url = str_replace("/{$scheme['host']}/", "/{$host_ip}/", $url);
                $headers[] = 'Host: ' . $scheme['host'];
            }
        }

        if (self::$headers) {
            $headers = array_merge(self::$headers, $headers);
            $headers = array_unique($headers);
        }

        // processing data
        if ($data !== null && is_array($data)) {
            if (stripos(self::$content_type, 'application/json') !== false) {
                $data = json_encode($data);
            } else if (stripos(self::$content_type, 'application/x-www-form-urlencoded') !== false) {
                $data = http_build_query($data);
            }
        }
    }
}
