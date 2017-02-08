<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * 系统请求路由处理类
 * @package Netap
 * @category System
 *
 */
class Netap_Request
{
    /**
     * @var  string  protocol: http, https, ftp, cli, etc
     */
    public static $protocol = 'http';

    /**
     *
     * @var string 当前url
     */
    public static $url;

    /**
     *
     * @var string 控制器类名
     */
    public static $controller;

    /**
     *
     * @var string 控制器方法
     */
    public static $action;

    /**
     * 控制器物理路径
     * @var string
     */
    public static $controller_path;

    /**
     *
     * @var string 参数
     */
    public static $args;

    /** 默认处理的最大位置标识  */
    private static $proc_pos = 3;

    const CONTROLLER_PREFIX = 'Controller_';

    /** 默认控制器  */
    const CONTROLLER_DEFAULT = 'index';

    const ACTION_PREFIX = 'action_';

    /** 默认方法  */
    const ACTION_DEFAULT = 'index';

    /**
     *
     * 控制器主入口
     * @param array $route_table
     * @param array $options array('proc_pos'=>'设置控制器目录最大层级')
     * @throws Netap_Exception
     * @throws Netap_NotFoundException
     */
    public static function execute(& $route_table = array(), $options = array())
    {
        if (isset($options['proc_pos'])) {
            self::$proc_pos = $options['proc_pos'];
        }

        self::$url = $url = Netap_Request::detect_uri();

        /* 处理路由表 */
        $url = self::processRouteTable($route_table, $url);

        $uri_array = explode('/', $url);

        if (!is_array($uri_array)) {
            $uri_array = array();
        }

        /* 清除第一个空格 */
        if (empty($uri_array[0])) {
            $uri_array = array_slice($uri_array, 1);
        }

        /* 不使用虚拟路径的情况  */
        if (isset($uri_array[0]) && $uri_array[0] == 'index.php') {
            $uri_array = array_slice($uri_array, 1);
        }

        /* 处理位置标识  */
        $proc_pos = min(sizeof($uri_array), max((int)self::$proc_pos, 1));
        $controller_exsit = false;

        if ($proc_pos) {
            while (--$proc_pos >= 0) {
                $path = array_merge(array(APPPATH, 'controllers'), array_slice($uri_array, 0, $proc_pos));
                self::$controller_path = implode(DIRECTORY_SEPARATOR, $path);
                if (self::controller_exsit($uri_array[$proc_pos])) {
                    $controller_exsit = true;
                    break;
                }
            }
        } else {
            self::$controller_path = APPPATH . DIRECTORY_SEPARATOR . 'controllers';
            if (self::controller_exsit()) {
                $controller_exsit = true;
            }
        }

        if ($controller_exsit) {
            self::$controller = empty($uri_array[$proc_pos]) ? self::CONTROLLER_PREFIX . ucfirst(self::CONTROLLER_DEFAULT) : self::CONTROLLER_PREFIX . ucfirst($uri_array[$proc_pos]);
            $action_name = empty($uri_array[$proc_pos + 1]) ? self::ACTION_DEFAULT : strtolower($uri_array[$proc_pos + 1]);
            self::$action = self::ACTION_PREFIX . $action_name;
            self::$args = array_slice($uri_array, $proc_pos + 2);
            self::do_controller(self::$controller, self::$action, self::$args, $action_name);
        } else {
            throw new Netap_NotFoundException('访问路径(1)：' . self::$url . ' 不存在!', 404);
        }
    }

    /**
     * 处理路由表
     * @param array $route_table
     * @param string $url
     * @internal param array $patterns
     * @return string
     */
    private static function processRouteTable(array &$route_table, $url = '')
    {
        foreach ($route_table as &$route) {
            if (strpos('|' . $route['verb'] . '|', '|' . self::getRequestMethod() . '|') === false && $route['verb'] != '*') {
                continue;
            }

            $url_param = self::urlMatch($route['pattern'], $url);
            if ($url_param !== false) {
                $url = $route['action'];
                if ($url_param) {
                    //将资源参数，追加到内部URL，支援方法参数
                    $url = rtrim($url, '/') . '/' . implode('/', $url_param);

                    //将资源参数，写入GET参数，若重复key则将GET覆盖
                    $_GET = array_merge($_GET, $url_param);
                }
                break;
            }
        }
        return $url;
    }


    /**
     * 获取请求方法
     *
     * @return string
     */
    public static function getRequestMethod()
    {
        $method = 'GET';

        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        return $method;
    }

    /**
     * URL解析，不支持数组形式
     *
     * @example
     * e.g. 1
     * $patten: /api/group/{$gid}/users/{$uid}/
     * $url:    /api/group/3/users/4/
     * $regex:    false
     * matched:    array('gid' => 3, 'uid' => 4)
     * e.g. 2
     * $patten: /\/api\/group\/(?<gid>[^\/]*)\/users/(?<uid>[^\/]*)\//ui
     * $url:    /api/group/3/users/4/
     * $regex:    true
     * matched:    array('gid' => 3, 'uid' => 4)
     *
     * @param $pattern
     * @param string $url
     * @param bool $regex
     * @return mixed boolean|array
     * @internal param string $patten
     */
    public static function urlMatch($pattern, $url, $regex = false)
    {
        if (!$regex) {
            $pattern = '#^' . preg_replace('#(/?)\{(\w+)\}(/?)#ui', '$1(?<$2>[^/]*)$3', $pattern) . '$#ui';
        }

        if (preg_match($pattern, $url, $matches)) {
            $params = array();
            foreach ($matches as $k => $v) {
                if (!is_int($k)) {
                    $params[$k] = $v;
                }
            }

            return $params;
        }

        return false;
    }

    /**
     *
     * 控制器是否存在
     * @param string $controller 控制器名称
     * @return bool
     */
    private static function controller_exsit($controller = '')
    {
        $result = false;
        $controller_uri = empty($controller) ? 'index' : $controller;
        self::$controller = self::CONTROLLER_PREFIX . ucfirst($controller_uri);
        if (class_exists(self::$controller)) {
            $result = true;
        }

        return $result;
    }

    /**
     *
     * 调用控制器方法
     * @param string $controller 控制器类
     * @param string $action 控制器方法
     * @param string $args 控制器方法参数
     * @param string $action_name 控制器方法名称
     * @throws Netap_Exception
     */
    private static function do_controller($controller, $action, $args, $action_name)
    {
        /* 创建控制器 */
        $ctl_ojb = new $controller();

        if (!method_exists($ctl_ojb, $action)) {
            /* 方法不存在，则调用默认方法index */
            $action = self::$action = self::ACTION_PREFIX . self::ACTION_DEFAULT;
            if (!method_exists($ctl_ojb, $action)) {
                throw new Netap_Exception('访问路径(2)：' . self::$url . ' 不存在!', 404);
            }

            //补回丢失的第一个参数
            array_unshift($args, $action_name);
            self::$args = $args;
        }

        /* 调用控制器前置方法  */
        if (method_exists($ctl_ojb, 'before')) {
            call_user_func(array(&$ctl_ojb, 'before'));
        }

        /* action_xxx 第一个参数默认值问题*/
        if (isset($args[0]) && $args[0] == '') {
            $args = array();
        }

        call_user_func_array(array(&$ctl_ojb, $action), $args);

        /* 调用控制器后置方法  */
        if (method_exists($ctl_ojb, 'after')) {
            call_user_func(array(&$ctl_ojb, 'after'));
        }
    }

    /**
     *
     * 获取当前规格化URL信息
     * @return string
     */
    public static function detect_uri()
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            /* 如果有路径的情况 */
            return $_SERVER['PATH_INFO'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = rawurldecode($uri);
        } elseif (isset($_SERVER['PHP_SELF'])) {
            $uri = $_SERVER['PHP_SELF'];
        } elseif (isset($_SERVER['REDIRECT_URL'])) {
            $uri = $_SERVER['REDIRECT_URL'];
        } else {
            return '';
        }

        $siteroot = SITEROOT;
        if (!empty($siteroot)) {
            /* 去掉末尾的斜杠 */
            $siteroot = rtrim($siteroot, '/');

            /* 去掉站点前缀  */
            if (!empty($siteroot)) {
                $uri = substr($uri, strlen($siteroot));
            }
        }

        return $uri;
    }
}
