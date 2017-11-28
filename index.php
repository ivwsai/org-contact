<?php
/**
 *
 *  程序框架主入口
 * @package Netap
 * @category Main
 * @author OAM Team
 * @copyright Copyright 1999-2013 © 91.com All rights reserved.
 *
 */

/* 框架版本  */
define('VERSION', '2.0');

/* 程序是否在调试 0=非调试 1=调试  */
define('IS_DEBUG', TRUE);

/* 站点URL前缀（控制器路径前缀），必须以/开始和结尾。如：  / ,  /netap2/ ,  /sites/netap2/   */
define('SITEROOT', '/');

/* 是否模拟程序，特定程序的调试开发使用如短信发送 ，正式生产环境请配置为FALSE */
define('IS_SIMULATE', FALSE);

/* 语言包选择  */
define('LANGUAGE', 'zh');

/* 定义常用路径  */
define('DOCROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('SYSPATH', DOCROOT . 'system');
define('MODPATH', DOCROOT . 'modules');
define('APPPATH', DOCROOT . 'application');

/* 日志定义  */
/* 日志记录级别 TRACE<DEBUG<INFO<WARN<ERROR<FATAL<OFF */
define('LOG_LEVEL', 'DEBUG');

/* 日志存放位置 EMAIL|FILE */
define('LOG_TYPE', 'FILE');

/* 日志记录路径 */
define('LOG_PATH', APPPATH . '/tempfiles/logs');

/* 日志记录发送邮箱  */
define('LOG_EMAIL', 'admin@test.com');

/* 调试时输出所有错误，运行时屏蔽部分底层错误 */
if (defined('IS_DEBUG') && IS_DEBUG) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

/* 核心环境初始化  */
require SYSPATH . '/classes/core.php';

/* 如果没在单元测试，则启动程序  */
if (!defined('IN_UNITTEST')) {
    require APPPATH . '/bootstrap.php';
}