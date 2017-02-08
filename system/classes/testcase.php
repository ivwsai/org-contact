<?php
require_once 'PHPUnit/Framework/TestCase.php';

/**
 *
 * 所有测试用例基类，用例的编写在此类基础上进行
 * @package Netap
 * @category Tests
 *
 */
abstract class Netap_TestCase extends PHPUnit_Framework_TestCase
{

    /**
     * 是否初始化过运行环境
     * @var boolean
     */
    private static $initenv = FALSE;

    /**
     * 准备测试环境
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * 清理测试环境
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->initenv();
    }

    /**
     * 初始化程序环境，使其和主框架运行环境一致
     */
    private function initenv()
    {
        if (!self::$initenv) {
            define('IN_UNITTEST', TRUE);
            require_once 'index.php';
            date_default_timezone_set('PRC');
            setlocale(LC_ALL, 'zh_CN.utf-8');
            Netap::init();
            self::$initenv = TRUE;
        }
    }
}

