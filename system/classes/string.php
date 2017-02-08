<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * 字符串处理帮助类
 * @package Netap
 * @category System
 * @author  OAM Team
 * @deprecated netap2不赞成继使用，字符处理类应该使用Netap_UTF8
 */
class Netap_String
{

    /**
     * 计算UTF-8字符集字符串长度
     * @param string $str 需计算的字符串
     * @param string $charset 设置的字符串编码，默认为utf-8
     * return int
     * @return int
     */
    public static function dstrlen($str, $charset = 'utf-8')
    {
        if (strtolower($charset) != 'utf-8') {
            return strlen($str);
        }
        $count = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $value = ord($str[$i]);
            if ($value > 127) {
                $count++;
                if ($value >= 192 && $value <= 223)
                    $i++;
                elseif ($value >= 224 && $value <= 239)
                    $i = $i + 2;
                elseif ($value >= 240 && $value <= 247)
                    $i = $i + 3;
            }
            $count++;
        }
        return $count;
    }

    /**
     * 判断是否为简体汉字
     * @param string $str 需判定的字符串
     * @return bool
     */
    public static function isgb($str)
    {
        if (self::dstrlen($str) >= 2) {
            $str = strtok($str, "");
            if (ord($str[0]) < 161 || ord($str[0]) > 247) {
                return false;
            } elseif (ord($str[1]) < 161 || ord($str[1]) > 254) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * UTF-8字符集字符串截取
     * @param string $string 需截取的字符串
     * @param int $length 需截取的字符串长度
     * @param string $dot 截取的字符串替代形式
     * @param string $charset 默认设置的字符编码
     * return string
     * @return string
     */
    public static function cutstr($string, $length, $dot = ' ...', $charset = 'utf-8')
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        $pre = chr(1);
        $end = chr(1);
        $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), $string);

        $strcut = '';
        if (strtolower($charset) == 'utf-8') {
            $n = $tn = $noc = 0;
            while ($n < strlen($string)) {

                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                } elseif (224 <= $t && $t <= 239) {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                } elseif (240 <= $t && $t <= 247) {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                } elseif (248 <= $t && $t <= 251) {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                } elseif ($t == 252 || $t == 253) {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                } else {
                    $n++;
                }

                if ($noc >= $length) {
                    break;
                }

            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            }
        }
        $strcut = str_replace(array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
        $pos = strrpos($strcut, chr(1));
        if ($pos !== false) {
            $strcut = substr($strcut, 0, $pos);
        }
        return $strcut . $dot;
    }

    /**
     * 转义处理
     * @param string $string
     * @param int $force
     * @param bool $strip
     * @return mixed
     */
    public static function addslashes($string, $force = 0, $strip = FALSE)
    {

        if (!ini_get('magic_quotes_gpc') || $force) {
            if (is_array($string)) {
                $temp = array();
                foreach ($string as $key => $val) {
                    $key = addslashes($strip ? stripslashes($key) : $key);
                    $temp[$key] = self::addslashes($val, $force, $strip);
                }
                $string = $temp;
                unset($temp);
            } else {
                $string = addslashes($strip ? stripslashes(trim($string)) : trim($string));
            }
        }
        return $string;
    }

    /**
     * Generates a random string of a given type and length.
     *
     *
     * $str = Text::random(); // 8 character random string
     *
     * The following types are supported:
     *
     * alnum
     * :  Upper and lower case a-z, 0-9 (default)
     *
     * alpha
     * :  Upper and lower case a-z
     *
     * hexdec
     * :  Hexadecimal characters a-f, 0-9
     *
     * distinct
     * :  Uppercase characters and numbers that cannot be confused
     *
     * You can also create a custom type by providing the "pool" of characters
     * as the type.
     *
     * @param   string   a type of pool, or a string of characters to use as the pool
     * @param   integer  length of string to return
     * @return  string
     * @uses    UTF8::split
     */
    public static function random($type = NULL, $length = 8)
    {
        if ($type === NULL) {
            // Default is to generate an alphanumeric string
            $type = 'alnum';
        }

        $utf8 = FALSE;

        switch ($type) {
            case 'alnum' :
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha' :
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec' :
                $pool = '0123456789abcdef';
                break;
            case 'numeric' :
                $pool = '0123456789';
                break;
            case 'nozero' :
                $pool = '123456789';
                break;
            case 'distinct' :
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default :
                $pool = (string)$type;
                $utf8 = !Netap_UTF8::is_ascii($pool);
                break;
        }

        // Split the pool into an array of characters
        $pool = ($utf8 === TRUE) ? Netap_UTF8::str_split($pool, 1) : str_split($pool, 1);

        // Largest pool key
        $max = count($pool) - 1;

        $str = '';
        for ($i = 0; $i < $length; $i++) {
            // Select a random character from the pool and add it to the string
            $str .= $pool[mt_rand(0, $max)];
        }

        // Make sure alnum strings contain at least one letter and one digit
        if ($type === 'alnum' and $length > 1) {
            if (ctype_alpha($str)) {
                // Add a random digit
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
            } elseif (ctype_digit($str)) {
                // Add a random letter
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
            }
        }

        return $str;
    }

    /**
     * 计算中文字符串长度 by mikko
     */
    function utf8_strlen($string = null)
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        return count($match[0]);
    }
}
