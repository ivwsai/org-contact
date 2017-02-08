<?php //defined('SYSPATH') or die ('No direct script access.');

/**
 *
 * 安全函数
 *
 * @package Netap
 * @category Helpers

 *
 */
class Helper_Security
{

    /**
     * 将明文密码转化为内部存储密码(云办公用)
     * @param $pwd 明文密码
     * @param $type 类型 1=内网  0=外网
     * @return 内部存储密码
     */
    public static function generate_password($pwd, $type = '0')
    {
        if ($type == '1') {
            return md5($pwd);
        }

        return md5(mb_convert_encoding($pwd . "，。fdjf,jkgfkl", "GB2312", "UTF-8"));
    }

    /**
     * 生成一个加密过的密码
     * @param string $password
     * @param int $level 层级 1, 2
     * @param string $type enum(SSHA,SHA,SMD5,MD5)
     * @return string
     */
    public static function generatePassword($password, $level = 2, $type = 'SSHA')
    {
        switch ($type) {
            case 'SSHA':
                $password = self::ssha($password, $level);
                break;
            case 'SHA':
                if ($level == 2) {
                    $password = sha1(sha1($password));
                } else {
                    $password = sha1($password);
                }
                break;
            case 'SMD5':
                $password = self::smd5($password, $level);
                break;
            case 'MD5':
                if ($level == 2) {
                    $password = md5(md5($password));
                } else {
                    $password = md5($password);
                }
                break;
            default:
                $password = self::ssha($password, $level);
        }

        return $password;
    }

    /**
     * 验证密码
     * @param string $password 密码明文
     * @param string $enPassword 加密后的密码
     * @param int $level 是否密码明文已经加密过一次
     * @param string $type 加密类型
     * @return bool
     */
    public static function validPassword($password, $enPassword, $level = 1, $type = 'SSHA')
    {
        switch ($type) {
            case 'SHA':
                $result = sha1($password) == $enPassword;
                break;
            case 'SMD5':
                $salt = substr($enPassword, -10);
                $result = (self::smd5($password, $level, $salt) == $enPassword);
                break;
            case 'MD5':
                $result = md5($password) == $enPassword;
                break;
            case 'SSHA':
            default:
                $salt = substr($enPassword, -10);
                $result = (self::ssha($password, $level, $salt) == $enPassword);
        }

        return $result;
    }

    /**
     * 按SSHA加密字符
     * @param string $password
     * @param int $level 层级
     * @param string $salt
     * @return string
     */
    public static function ssha($password, $level = 2, $salt = '')
    {

        if (empty($salt)) {
            for ($i = 1; $i <= 10; $i++) {
                $salt .= substr("0123456789abcdef", rand(0, 15), 1);
            }
        }

        if ($level == 1) {
            $hash = sha1($password . $salt) . $salt;
        } else {
            $hash = sha1(sha1($password) . $salt) . $salt;
        }

        return $hash;
    }

    /**
     * 按SMD5加密字符
     * @param string $password
     * @param int $level 层级
     * @param string $salt
     * @return string
     */
    public static function smd5($password, $level = 2, $salt = '')
    {

        if (empty($salt)) {
            for ($i = 1; $i <= 10; $i++) {
                $salt .= substr("0123456789abcdef", rand(0, 15), 1);
            }
        }

        if ($level == 1) {
            $hash = md5($password . $salt) . $salt;
        } else {
            $hash = md5(md5($password) . $salt) . $salt;
        }

        return $hash;
    }

    /**
     * k12解密
     * @param string $crypttext
     * @return string
     */
    public static function rsa_decrypt($crypttext)
    {
        $text = base64_decode($crypttext);
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----
MIIBOgIBAAJBALns2qEz4pWVWMz6e/sV6A4Ugn1STQKtfszT6s577jV6XLr8jzde
4R1SxfZrgrO72JDjIErSQTxn2G37kqgCW+8CAwEAAQJAMNUZ0y8qevk/2o6Lk7X8
Pf57C2lbWrGw1SFv0Y3RUe//2SHcI5x7YRMGc+uelbEudYFppp4lesD1D3KsnbLp
OQIhAN/OEPQpyIA8u+DOVKydSor7Yv0Iuga2W52DDQP/VMJ9AiEA1KvQHgcrCzu4
04iUrgBvTtKz5EIF9riyawJf9X8d19sCIGjTM82o5GhsCfO5sJ9I7Ok75ZluxPLv
5ulXwHjm1uRhAiEAlGjJtQq/iPlGQ6feSYbYJiN3keRZERFWwMJJgKISi10CIDg9
1oQtaz0VOCPJKRGlvbx8uOKeTxrMv43B8qAlKrrt
-----END RSA PRIVATE KEY-----";
        openssl_private_decrypt($text, $decrypted, $privateKey);
        return $decrypted;
    }
}
