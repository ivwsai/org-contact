<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Request class
 *
 * @package Module

 * @copyright (c) 2010-2011 OAM Team
 *
 * Base on Curl Library by Matt Wells(www.ninjapenguin.co.uk)
 */
class Module_Request
{
    private $resource = null;
    private static $http_code = null;
    private static $client_ip = null;

    /**
     * Factory Method
     */
    public static function factory($curl_options = array())
    {
        return new Module_Request($curl_options);
    }

    /**
     * Constructor
     */
    public function __construct($curl_options = array())
    {
        if (!function_exists('curl_init')) {
            throw new Exception('A cURL error occurred, It appears you do not have cURL installed!');
        }

        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            self::$client_ip = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            self::$client_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            self::$client_ip = $_SERVER["REMOTE_ADDR"];
        }

        $config = array(
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "PHP CURL REQUEST",
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_REFERER => isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""
        );

        // Apply any passed configuration
        $config = $curl_options + $config;

        $this->resource = curl_init();

        // Apply configuration settings
        foreach ($config as $key => $value) {
            $this->set_opt($key, $value);
        }
    }

    /**
     * Set option
     *
     * @param String Curl option to set
     * @param String Value for option
     * @return $this
     */
    private function set_opt($key, $value)
    {
        curl_setopt($this->resource, $key, $value);
        return $this;
    }

    /**
     * Execute the curl request and return the response
     *
     * @return String Returned output from the requested resource
     * @throws Exception
     */
    private function exec()
    {
        $ret = curl_exec($this->resource);

        // Wrap the error reporting in an exception
        if ($ret === false) {
            throw new Exception("Curl Error, " . curl_error($this->resource));
        } else {
            self::$http_code = curl_getinfo($this->resource, CURLINFO_HTTP_CODE);
            return $ret;
        }
    }

    /**
     * Get Error
     * Returns any current error for the curl request
     *
     * @return string The error
     */
    private function get_error()
    {
        return curl_error($this->resource);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        curl_close($this->resource);
    }

    /**
     * Get
     * Execute an HTTP GET request using curl
     *
     * @param String url to request
     * @param Array additional headers to send in the request
     * @param Bool flag to return only the headers
     * @param Array Additional curl options to instantiate curl with
     * @return RESTResponse
     */
    public static function get($url, Array $headers = array(), $headers_only = false, Array $curl_options = array())
    {
        $ch = Module_Request::factory($curl_options);

        $ch->set_opt(CURLOPT_URL, $url)
            ->set_opt(CURLOPT_RETURNTRANSFER, true)
            ->set_opt(CURLOPT_NOBODY, $headers_only);

        self::setHeaders($ch, $headers);
        $result['content'] = $ch->exec();
        $result['http_code'] = self::$http_code;
        unset($ch);

        return new RESTResponse($result);
    }

    /**
     * Post
     * Execute an HTTP POST request, posting the past parameters
     *
     * @param String url to request
     * @param Mix past data to post to $url
     * @param Array additional headers to send in the request
     * @param Bool flag to return only the headers
     * @param Array Additional curl options to instantiate curl with
     * @return RESTResponse
     */
    public static function post($url, $data = '', Array $headers = array(), $headers_only = false, Array $curl_options = array())
    {
        $ch = Module_Request::factory($curl_options);

        $ch->set_opt(CURLOPT_URL, $url)
            ->set_opt(CURLOPT_RETURNTRANSFER, true)
            ->set_opt(CURLOPT_NOBODY, $headers_only)
            ->set_opt(CURLOPT_POST, true)
            ->set_opt(CURLOPT_POSTFIELDS, $data);

        self::setHeaders($ch, $headers);
        $result['content'] = $ch->exec();
        $result['http_code'] = self::$http_code;
        unset($ch);

        return new RESTResponse($result);
    }

    /**
     * Put
     * Execute an HTTP PUT request, posting the past parameters
     *
     * @param String $url
     * @param String $data
     * @param Array $headers
     * @param Bool $headers_only
     * @param Array $curl_options
     * @throws Exception
     * @return RESTResponse
     */
    public static function put($url, $data = null, Array $headers = array(), $headers_only = false, Array $curl_options = array())
    {
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $put_file = @tmpfile();
        if (!$put_file) {
            throw new Exception('Tmpfile Error, Could not create tmpfile for PUT operation');
        }
        fwrite($put_file, $data);
        fseek($put_file, 0);

        $ch = Module_Request::factory($curl_options);

        $ch->set_opt(CURLOPT_URL, $url)
            ->set_opt(CURLOPT_RETURNTRANSFER, true)
            ->set_opt(CURLOPT_NOBODY, $headers_only)
            ->set_opt(CURLOPT_PUT, true)
            ->set_opt(CURLOPT_INFILE, $put_file)
            ->set_opt(CURLOPT_INFILESIZE, strlen($data));

        self::setHeaders($ch, $headers);
        $result['content'] = $ch->exec();
        $result['http_code'] = self::$http_code;
        unset($ch);

        return new RESTResponse($result);
    }

    /**
     * Patch
     * Execute an Patch Delete request, posting the past parameters
     *
     * @param String $url
     * @param String $data to post to $url
     * @param Array $headers
     * @param Bool $headers_only
     * @param Array $curl_options
     * @throws Exception
     * @return RESTResponse
     */
    public static function patch($url, $data = null, Array $headers = array(), $headers_only = false, Array $curl_options = array())
    {
        if ($data && !is_string($data)) {
            throw new Exception('Module_Request::patch() expects parameter 2 to be string, array given');
        }

        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        $ch = Module_Request::factory($curl_options);

        $ch->set_opt(CURLOPT_URL, $url)
            ->set_opt(CURLOPT_RETURNTRANSFER, true)
            ->set_opt(CURLOPT_NOBODY, $headers_only)
            ->set_opt(CURLOPT_POSTFIELDS, $data)
            ->set_opt(CURLOPT_CUSTOMREQUEST, 'PATCH');

        self::setHeaders($ch, $headers);
        $result['content'] = $ch->exec();
        $result['http_code'] = self::$http_code;
        unset($ch);

        return new RESTResponse($result);
    }

    /**
     * Delete
     * Execute an HTTP Delete request, posting the past parameters
     *
     * @param String $url
     * @param Array $headers
     * @param Bool $headers_only
     * @param Array $curl_options
     * @return RESTResponse
     */
    public static function delete($url, Array $headers = array(), $headers_only = false, Array $curl_options = array())
    {
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $ch = Module_Request::factory($curl_options);

        $ch->set_opt(CURLOPT_URL, $url)
            ->set_opt(CURLOPT_RETURNTRANSFER, true)
            ->set_opt(CURLOPT_NOBODY, $headers_only)
            ->set_opt(CURLOPT_CUSTOMREQUEST, 'DELETE');

        self::setHeaders($ch, $headers);
        $result['content'] = $ch->exec();
        $result['http_code'] = self::$http_code;
        unset($ch);

        return new RESTResponse($result);
    }

    /**
     * 设置请求头
     * @param resource $ch
     * @param array $headers
     * @return void
     */
    private static function setHeaders(&$ch, $headers)
    {
        // Set any additional headers.
        if (self::$client_ip) {
            $headers = array_merge($headers, array(
                "CLIENT-IP: " . self::$client_ip,
                "X-FORWARDED-FOR: " . self::$client_ip
            ));
        }

        if (!empty($headers)) {
            $ch->set_opt(CURLOPT_HTTPHEADER, $headers);
        }
    }
}

/**
 * RESTResponse class
 *
 * @package Module
 *
 */
class RESTResponse
{
    private $content;
    private $http_code;

    public function __construct($response)
    {
        $this->content = $response['content'];
        $this->http_code = $response['http_code'];
    }

    /**
     * @return string
     */
    public function to_normal()
    {
        return $this->content;
    }

    /**
     * @return SimpleXMLElement
     */
    public function to_xml()
    {
        return simplexml_load_string($this->content);
    }

    /**
     * @param bool|string $assoc
     * @return mixed
     */
    public function to_json($assoc = true)
    {
        return json_decode($this->content, $assoc);
    }

    /**
     * @return int
     */
    public function get_http_code()
    {
        return $this->http_code;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ( string )$this->get_http_code();
    }
}
