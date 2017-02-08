<?php
namespace libraries\example\service;

use Netap_Config;
use Netap_Service;

defined('SYSPATH') or die('No direct script access.');

/**
 *
 *  样例业务层
 * @package Netap
 * @category Example
 *
 */
class Example extends Netap_Service
{
    /**
     *
     * @return array
     */
    public function proc_buss1()
    {
        $cache_cfg = Netap_Config::config('cache', 'libraries\example\config');
        print_r($cache_cfg);
        return true;
    }
}