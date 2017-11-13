<?php defined('SYSPATH') or die('No direct access');
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https://" : "http://";
return array(
    'site_version' => '1.0',                  //工程名称，主要用于js/css缓存的版本控制
    'theme' => '/static/themes/default/',     //项目皮肤主要有 /static/default/(css|images|(js/user|etc..))
    'js_lib' => '/static/lib/',
    'base_url' => $protocol.$_SERVER['HTTP_HOST'], 
);