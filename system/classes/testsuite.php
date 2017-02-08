<?php
require_once 'PHPUnit/Framework/TestSuite.php';

/**
 *
 * 测试集合基类，可以将多个测试串联起来进行调测，以提高单元测试效率
 * @package Netap
 * @category Tests
 * @author OAM Team
 *
 */
class Netap_TestSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * 构建测试集调用句柄
     * @example
     * $this->setName( '测试集合名称' );
     * $this->addTestSuite( '测试名称' );
     *
     */
    public function __construct()
    {
    }

    /**
     * 创建测试集合
     */
    public static function suite()
    {
        return new self ();
    }
}

