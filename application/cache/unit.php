<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * 主要用来实现一些控制器公用方法调用
 *
 */
class Cache_Unit
{
    /**
     * 读取缓存项 GLOBAL_CACHE_SIGN_SYSITEMS_$UNITID
     * @param int $unitid
     * @return Ambigous <multitype:, query, object, multitype:unknown >
     */
    public static function getCache_GLOBAL_CACHE_SIGN_SYSITEMS($unitid)
    {
        $key = 'GLOBAL_CACHE_SIGN_SYSITEMS_' . $unitid;
        $cacheds = new Netap_Cache('default');
        $unitlist = $cacheds->get($key);

        if (is_null($unitlist)) {  //先读缓存
            $model_example = new Model_Example('default');
            $unitlist = $model_example->get_unit_list();
            $cacheds->set($key, $unitlist);
        }

        return $unitlist;
    }

    /**
     * 删除缓存项 GLOBAL_CACHE_SIGN_SYSITEMS_$UNITID
     * @param int $unitid
     */
    public static function removeCache_GLOBAL_CACHE_SIGN_SYSITEMS($unitid)
    {
        $key = 'GLOBAL_CACHE_SIGN_SYSITEMS_' . $unitid;
        $cacheds = new Netap_Cache('default');
        $cacheds->delete($key);
    }
}
