<?php defined('SYSPATH') or die('No direct access');
/**
 *
 * 启动加载项，用户可以根据需要修改
 * @package Netap
 * @category Main
 *
 */

/** 设置时区－中国标准时区  Etc/GMT+8 - 东八区 */
date_default_timezone_set('PRC');

/** 设置本地化区域及字符集 */
setlocale(LC_ALL, 'zh_CN.utf-8');

/** 初始化程序 */
Netap::init();

/**
 * 可选路由表，顺序匹配，完全匹配。
 * 形如{id}为用于匹配的占位符，解析出 id 的值追加合并至$_GET变量
 **/
$route_table = array(
    array('pattern' => '/api/resources', 'verb' => 'POST', 'action' => '/example/resources/create'),
    array('pattern' => '/api/resources', 'verb' => 'GET|PUT', 'action' => '/example/resources/gets'),
    array('pattern' => '/api/resources/{id}', 'verb' => '*', 'action' => '/example/resources/get'),
    array('pattern' => '/api/resources/{id}', 'verb' => 'PATCH', 'action' => '/example/resources/patch'),
    array('pattern' => '/api/resources/{id}', 'verb' => 'PUT', 'action' => '/example/resources/put'),
    array('pattern' => '/api/resources/{id}', 'verb' => 'DELETE', 'action' => '/example/resources/delete'),
    array('pattern' => '/api/resources/{uid}/actions/{gid}', 'verb' => 'PUT|GET', 'action' => '/example/config'),
    //错误处理示例
    //array('pattern'=>'/api/.*', 'verb'=>'GET|POST|PATCH|PUT|DELETE|HEAD|OPTION', 'action'=>'example/resources/not_found'),
);
$route_table = array();

/** 启动程序 */
Netap_Request::execute($route_table);
