<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * 第三方手机识别类
 * Official page: http://mobiledetect.net
 */

require_once 'vender/Mobile_Detect.php';

/**
 *  检测浏览器类型，是手机、平板还是计算机
 * @example
 * <p>
 * $deviceType = (Module_Mobiledetect::isMobile() ? (Module_Mobiledetect::isTablet() ? 'tablet' : 'phone') : 'computer');
 * </p>
 */
class Module_Mobiledetect
{
    /**
     *
     * @var object
     */
    private static $mobiledetect = NULL;

    /**
     * 单例
     * @return Mobile_Detect
     */
    private static function getInstance()
    {
        if (empty(self::$mobiledetect)) {
            self::$mobiledetect = new Mobile_Detect();
        }
        return self::$mobiledetect;
    }

    /**
     * 是否手机
     * @return boolean
     */
    public static function isMobile()
    {
        return self::getInstance()->isMobile();
    }

    /**
     * 是否平板
     * @return boolean
     */
    public static function isTablet()
    {
        return self::getInstance()->isTablet();
    }

    /**
     * 是否iOS
     * @return boolean
     */
    public static function isiOS()
    {
        return self::getInstance()->isiOS();
    }

    /**
     * 是否Android
     * @return boolean
     */
    public static function isAndroidOS()
    {
        return self::getInstance()->isAndroidOS();
    }
}
