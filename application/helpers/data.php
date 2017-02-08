<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 * 格式化数据
 *
 * @package Netap
 * @category Helpers

 *
 */
class Helper_Data
{

    /**
     * 格式化类据类型
     *
     * @access public
     * @param array $arr
     * @param array $declaration 类型声明
     * @param bool $is_multi 是否多条记录
     * @return array
     */
    public static function formatType(array $arr, array $declaration, $is_multi = true)
    {
        $data = $is_multi ? $arr : array($arr);
        foreach ($data as $k => &$v) {
            foreach ($v as $key => &$val) {
                if (!isset($declaration[$key])) {
                    $val = (string)$val;
                    continue;
                }

                switch (gettype($declaration[$key])) {
                    case 'boolean':
                        $val = (bool)$val;
                        break;
                    case 'integer':
                        $val = (int)$val;
                        break;
                    case 'double':
                        $val = sprintf('%F', $val);
                        break;
                    case 'string':
                        $val = (string)$val;
                        break;
                    case 'array':
                        break;
                    default:
                        $val = (string)$val;
                        break;
                }
            }
        }

        return $is_multi ? $data : $data[0];
    }

    /**
     * 字节转换为方便阅读的格式
     * @param int $num
     * @param int $precision
     * @return string
     */
    public static function formatByte($num, $precision = 1)
    {

        if ($num >= 1000000000000) {
            $num = round($num / 1099511627776, $precision);
            $unit = 'TB';
        } elseif ($num >= 1000000000) {
            $num = round($num / 1073741824, $precision);
            $unit = 'GB';
        } elseif ($num >= 1000000) {
            $num = round($num / 1048576, $precision);
            $unit = 'MB';
        } elseif ($num >= 1000) {
            $num = round($num / 1024, $precision);
            $unit = 'KB';
        } else {
            $unit = 'Bytes';
            return number_format($num) . ' ' . $unit;
        }

        return number_format($num, $precision) . ' ' . $unit;
    }

    /**
     * 格式化剩余时间
     * @param int $time
     * @return string
     */
    public static function formatExcessTime($time)
    {
        $time = intval($time);

        if ($time <= 0) {
            return '0分';
        }

        $eday = floor($time / 60 / 60 / 24);
        $ehour = floor(($time / 60 / 60) % 24);
        $eminute = floor(($time / 60) % 60);

        return sprintf("%s天%s时%s分", $eday, $ehour, $eminute);
    }
}