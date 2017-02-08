<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * 验证规则类 与业务紧密结合的
 *
 * @package Unit
 * @category Helpers

 *
 */
class Helper_Verify
{

    /**
     * 工号规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function workid($value)
    {
        return (bool)preg_match("#^[0-9a-zA-Z\.\-_]{1,20}$#D", $value);
    }

    /**
     * 座位号规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function seat($value)
    {
        return (bool)preg_match("#^[^<>'\"\\?/]{1,20}$#uD", $value);
    }

    /**
     * 职务规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function duty($value)
    {
        return (bool)preg_match("#^[^<>'\"\\?/]{1,20}$#uD", $value);
    }

    /**
     * 部门规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function dept($value)
    {
        return (bool)preg_match("#^[^<>'\"\\?/]{1,50}$#uD", $value);
    }

    /**
     * 职员名称规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function staffName($value)
    {
        if (preg_match('#([税|發|发|开].*票)|[\d\-_\.\*０１２３４５６７８９]{7,}#', $value)) {
            return false;
        }

        return (bool)preg_match("#^[^<>'\"\\\?/]{1,50}$#uD", $value);
    }

    /**
     * 单位名称规则验证
     *
     * @param string $value
     * @return int   1:字符串长度不合法   2：非法单位名称 200：合法
     * @author wangmingkui
     */
    public static function unitName($value)
    {
        // 检查单位名称是否为合法字符构成
        //$code=200;合法
        $length = Netap_UTF8::strlen($value);
        if ($length <= 1 || $length > 50) {
            return 1;
        }
        if (preg_match("/[<>\'\"\/\\\?]/", $value)) {
            return 2;
        }
        if (preg_match("/([^\\x80-\\xff\\w])|([0-9]{1})+$/", $value) >= 1) {//增加只允许汉字、字母、下划线
            return 2;
        }
        //以下用来判断非法中文
        if (preg_match('/發.*票/', $value) >= 1) {
            return 2;
        }
        if (preg_match('/发.*票/', $value) >= 1) {
            return 2;
        }
        if (preg_match('/税.*单/', $value) >= 1) {
            return 2;
        }
        if (preg_match('/报.*关/', $value) >= 1) {
            return 2;
        }
        if (preg_match('/代.*开/', $value) >= 1) {
            return 2;
        }
        if (preg_match('/1[345789]\d{9}/', $value) >= 1) {
            return 2;
        }

        return 200;
    }

    /**
     * 帐号规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function account($value)
    {
        if (stripos($value, '@') !== FALSE) {
            return Helper_Verify::emailAccount($value);
        } else {
            return Helper_Verify::mobileAccount($value);
        }
    }

    /**
     * 角色规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function role($value)
    {
        return (bool)preg_match("#^[^<>'\"\\?/]{1,20}$#uD", $value);
    }

    /**
     * 群名规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function groupName($value)
    {
        return (bool)preg_match("#^[^<>'\"\\?/]{1,50}$#uD", $value);
    }

    /**
     * 自定义应用名称规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function appName($value)
    {
        return (bool)preg_match("#^[^<>'\"\\?/]{1,50}$#uD", $value);
    }

    /**
     * 唯一标识规则验证
     *
     * @param string $value
     * @return boolean
     */
    public static function flag($value)
    {
        throw new Exception('Did not complete.');
    }

    /**
     * 手机帐号规则
     *
     * @param int $value
     * @return boolean
     */
    public static function mobileAccount($value)
    {
        return (bool)preg_match("#^1[0-9]{10}$#uD", $value);
    }

    /**
     * 邮箱帐号规则
     *
     * @param string $email
     * @return boolean
     */
    public static function emailAccount($email)
    {
        if (preg_match("#[A-Z]#", $email)) {
            return FALSE;
        }

        return Helper_Valid::email($email);
    }

    /**
     * 企业域名规则
     *
     * @param string $domain
     * @return boolean
     */
    public static function domain($domain)
    {
        return (bool)preg_match("#^[一-龥\w\-_\.]{1,50}$#uD", $domain);
    }

    /**
     * 单位代码规则
     *
     * @param string $unitcode
     * @return bool
     */
    public static function unitCode($unitcode)
    {
        return strlen($unitcode) > 0 ? true : false;
    }
}
