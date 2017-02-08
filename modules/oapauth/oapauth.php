<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * OAP统一会话模块
 */

require_once MODPATH . '/oapauth/vender/unifyauth.php';
require_once MODPATH . '/oapauth/vender/oapauth.php';

/**
 *  OAP统一会话模块
 */
class Module_OapAuth
{
    /**
     * 获取会话高层API
     * @param string $sid
     * @param array $cfg_unifyauth
     * @return OapAuth
     */
    public static function getOapAuth($sid = NULL, array $cfg_unifyauth = NULL)
    {
        $cfg_unifyauth = $cfg_unifyauth ? $cfg_unifyauth : Netap_Config::load('unifyauth.cfg');
        $oapauth = new OapAuth($sid, $cfg_unifyauth);
        return $oapauth;
    }

    /**
     * 获取会话底层API
     * @param array $cfg_unifyauth
     * @return UnifyAuth
     */
    public static function getUnifyAuth(array $cfg_unifyauth = NULL)
    {
        $cfg_unifyauth = $cfg_unifyauth ? $cfg_unifyauth : Netap_Config::load('unifyauth.cfg');
        $oapauth = new UnifyAuth($cfg_unifyauth);
        return $oapauth;
    }
}
