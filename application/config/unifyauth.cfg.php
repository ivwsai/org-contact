<?php
return array(
    'lifetime' => 3600, //会话过期时间 ，单位秒,
    'limitsize' => 64 * 1024,  //每个会话大小限制 64K
    'save_handler' => 1,  //会话存储方式 0=file 1=php_memcached  2=php_memcache  注意：php_memcached和php_memcache不能混用，否则会发生异常
    'save_path' => array(
        array('path' => '192.168.94.21:11211', 'weight' => 1),
        array('path' => '192.168.94.21:11212', 'weight' => 1),
        //array('path'=>'d:/saas/session/','weight'=>1),
        //array('path'=>'d:/saas/session/','weight'=>1),
    ),
    'save_hash_strategy' => 0,  //会话存储哈希策略 0=CRC32 暂不支持其他策略
    'sid_function' => 0, //会话编号格式，0=MD5 128bits 1=SHA-1 160bits
    'session_cookie' => array(
        'cookie_enabled' => 1,          //是否开启浏览器COOKIE存储会话标识 0关闭 1开启
        'cookie_name' => 'OAPSID',  //session的cookie名称，cookie_enabled开启后生效
        'cookie_name_compatible' => 'PHPSESSID',  //session的cookie兼容名称，空为不
        'cookie_domain' => '',           //session的cookie所在域，空为当前域
    ),
);