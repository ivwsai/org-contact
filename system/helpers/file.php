<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 文件/文件夹帮助类
 *
 * @package    Netap
 * @category   Helpers
 */
class Helper_File
{

    /**
     * 创建多级目录
     * @param string $dir 待建立目录
     * @return bool
     */
    public static function mkdirs($dir)
    {
        if (!is_dir($dir)) {
            /* 递归新建目录 */
            if (!self::mkdirs(dirname($dir))) {
                return false;
            }

            /* 新建目录 */
            if (!mkdir($dir, 0777)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除多级目录
     * @param string $dir 待删除目录
     */
    public static function rmdirs($dir)
    {
        $d = dir($dir);
        while (false !== ($child = $d->read())) {
            if ($child != '.' && $child != '..') {
                if (is_dir($dir . '/' . $child)) {
                    /* 递归删除目录 */
                    self::rmdirs($dir . '/' . $child);
                } else {
                    /* 删除目录 */
                    unlink($dir . '/' . $child);
                }
            }
        }
        $d->close();
        rmdir($dir);
    }

}
