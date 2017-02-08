<?php defined('SYSPATH') or die('No direct script access.');

class Module_Unique
{

    /**
     * 生成指定长度随机ID
     *
     * @access public
     * @param int $len
     * @return string
     */
    public static function id($len = 10)
    {
        $seeds = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");

        $uuid = '';
        $len = $len <= 0 ? 10 : $len;
        for ($i = 0; $i < $len; $i++) {
            if ($i % 2 == 0) {
                $uuid = $seeds[mt_rand(0, 35)] . $uuid;
            } else {
                $uuid .= $seeds[mt_rand(0, 35)];
            }
        }
        return $uuid;
    }

    /**
     * 取得由时间截+进程ID+4组随机数生成的GUID
     *
     * @access public
     * @return string
     */
    public static function generatorCombGuid()
    {
        $time = microtime(true);
        $pid = getmypid();
        $time = explode('.', $time);

        $str = sprintf('%08x%04x%04x%04x%04x%04x%04x', $time[0], $time[1], $pid, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        return $str;
    }

    /**
     * 取得由8组随机数生成的GUID
     *
     * @access public
     * @return string
     */
    public static function guid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), // 16 bits for "time_mid"
            mt_rand(0, 0xffff), // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000, // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000, // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    /**
     * 取得uniqid生成后的ID使用md5格式化为32位长
     *
     * @access public
     * @return string
     */
    public static function create_guid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45); // "-"

        //$uuid = chr(123)// "{"
        $uuid = ""
            . substr($charid, 0, 8)
            . $hyphen . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        //.chr(125);// "}"

        return $uuid;
    }
}