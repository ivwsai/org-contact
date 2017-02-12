<?php defined('SYSPATH') or die('No direct access');
return array(
    'site_version' => '1.0',                  //工程名称，主要用于js/css缓存的版本控制
    'theme' => '/static/themes/default/',     //项目皮肤主要有 /static/default/(css|images|(js/user|etc..))
    'js_lib' => '/static/lib/',
    'base_url' => "http://".$_SERVER['HTTP_HOST'],  //站点访问地址eg:http://localhost or localhost/oa/(兼容旧版本使用)
);