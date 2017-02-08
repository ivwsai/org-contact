<?php defined('SYSPATH') or die('No direct script access.');

class Module_Excel
{

    public static function initExcel()
    {
        /** PHPExcel root directory */
        if (!defined('PHPEXCEL_ROOT')) {
            define('PHPEXCEL_ROOT', dirname(__FILE__) . '/');
            require_once(PHPEXCEL_ROOT . 'PHPExcel.php');
            require_once(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
            PHPExcel_Autoloader::Register();
            PHPExcel_Shared_ZipStreamWrapper::register();
            // check mbstring.func_overload
            if (ini_get('mbstring.func_overload') & 2) {
                throw new Exception('Multibyte function overloading in PHP must be disabled for string functions (2).');
            }
        }
    }

    //excel日期转换函数
    public static function excelTime($date, $time = false)
    {
        if (function_exists('GregorianToJD')) {
            if (is_numeric($date)) {
                $jd = GregorianToJD(1, 1, 1970);
                $gregorian = JDToGregorian($jd + intval($date) - 25569);
                $date = explode('/', $gregorian);
                $date_str = str_pad($date [2], 4, '0', STR_PAD_LEFT)
                    . "-" . str_pad($date [0], 2, '0', STR_PAD_LEFT)
                    . "-" . str_pad($date [1], 2, '0', STR_PAD_LEFT)
                    . ($time ? " 00:00:00" : '');
                return $date_str;
            }
        } else {
            $date = $date > 25568 ? $date + 1 : 25569;
            /*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
            $ofs = (70 * 365 + 17 + 2) * 86400;
            $date = date("Y-m-d", ($date * 86400) - $ofs) . ($time ? " 00:00:00" : '');
        }
        return $date;
    }
}
