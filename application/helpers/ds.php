<?php

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/1/31
 * Time: 下午10:59
 */
class Helper_Ds
{
    /**
     * 获取全局机构数据源配置
     *
     * @return array
     * @author tantan
     */
    public static function getGlobalDS()
    {
        return self::getDSFromConfig('default');
    }

    /**
     * 根据数据库配置获取配置项
     *
     * @param string $cfgname
     * @return array
     * @throws Netap_Exception
     * @author tantan
     */
    private static function getDSFromConfig($cfgname)
    {
        $dbcfg = Netap_Config::config('database');

        if (!is_array($dbcfg[$cfgname])) {
            throw new Netap_Exception ('找不到数据源错误:' . $cfgname . '，请联系管理员');
        }

        $ds = $dbcfg[$cfgname];
        return $ds;
    }
}