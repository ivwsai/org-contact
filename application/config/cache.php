<?php defined('SYSPATH') or die('No direct script access.');
return array(
    //全局网络缓存
    'default' => array(
        // KEY前缀
        'prefix' => '',
        // 缓存过期时间，单位秒 默认12小时
        'lifetime' => 43200,
        //默认缓存类型，memcache/filecache程序指定缓存类型后失效
        'driver' => 'filecache',
        //memcache配置（服务器组）
        'memcache' => array(
            'servers' => array(
                array(
                    'host' => '192.168.94.21',
                    'port' => 11211,
                    'persistent' => TRUE,
                    'weight' => 1,
                    'timeout' => 1,
                    'retry_interval' => 15,
                    'status' => TRUE,
                ),
                array(
                    'host' => '192.168.94.21',
                    'port' => 11212,
                    'persistent' => TRUE,
                    'weight' => 1,
                    'timeout' => 1,
                    'retry_interval' => 15,
                    'status' => TRUE,
                ),
            ),
            'driver' => 'memcached' //使用memcached或memcache扩展
        ),
        //filecache配置（存储目录前缀）
        'filecache' => array(
            //文件缓存配置目录
            'path' => APPPATH . '/tempfiles/cache_global',
        ),
    ),
    //本地缓存
    'local' => array(
        // KEY前缀
        'prefix' => '',
        // 缓存过期时间，单位秒 默认12小时
        'lifetime' => 43200,
        // 默认缓存类型，memcache/filecache程序指定缓存类型后失效
        'driver' => 'filecache',
        //memcache配置（服务器组）
        'memcache' => array(
            'servers' => array(
                array(
                    'host' => '192.168.94.21',
                    'port' => 11211,
                    'persistent' => TRUE,
                    'weight' => 1,
                    'timeout' => 1,
                    'retry_interval' => 15,
                    'status' => TRUE,
                ),
                array(
                    'host' => '192.168.94.21',
                    'port' => 11211,
                    'persistent' => TRUE,
                    'weight' => 1,
                    'timeout' => 1,
                    'retry_interval' => 15,
                    'status' => TRUE,
                ),
            ),
            'driver' => 'memcached' //使用memcached或memcache扩展
        ),
        //filecache配置（存储目录前缀）
        'filecache' => array(
            //文件缓存配置目录
            'path' => APPPATH . '/tempfiles/cache_local',
        ),
    ),
);