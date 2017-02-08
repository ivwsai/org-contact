<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * Netap_Controller 控制类基类
 * @package Netap
 * @category System
 * @author OAM Team
 *
 */
abstract class Netap_Controller
{

    /**
     * @return array
     */
    protected function uri_to_assoc()
    {
        $tmp = array();
        $array = Netap_Request::$args;
        $total = count($array);
        for ($i = 0; $i < $total; $i = $i + 2) {
            $tmp[$array[$i]] = isset($array[$i + 1]) ? $array[$i + 1] : '';
        }

        return $tmp;
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function uri_segment_value($key, $default = NULL)
    {
        $index = array_search($key, Netap_Request::$args);
        if ($index === FALSE) {
            return FALSE;
        }

        if (!isset(Netap_Request::$args[$index + 1])) {
            return $default;
        }

        return Netap_Request::$args[$index + 1];
    }

    public function before()
    {
    }

    public function action_index()
    {
        throw new Netap_NotFoundException();
    }

    public function after()
    {
    }
}
