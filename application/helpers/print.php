<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 * 打印调试信息用
 *
 * @package Netap
 * @category Helpers

 *
 */
class Helper_Print
{
    /**
     * DC.L 打印调试信息用
     *
     * @access public
     * @return void
     */
    public static function trace()
    {
        $args = func_get_args();
        $string = "\n";

        foreach ($args as $val) {
            $string .= print_r($val, true);
            $string .= "\n\n";
        }

        $string = highlight_string('<?php' . $string . '?' . '>', true);
        $string = str_replace('<span style="color: #0000BB">&lt;?php<br /></span>', '', $string);
        $string = str_replace('&lt;?php<br />', '', $string);
        $string = str_replace('<span style="color: #0000BB">?&gt;</span>', '', $string);
        $string = str_replace('<br />?&gt;', '', $string);
        echo $string;
    }

    /**
     * DC.L 打印调试信息用
     *
     * @access public
     * @return void
     */
    public static function export()
    {
        $args = func_get_args();
        $string = "\n";

        foreach ($args as $val) {
            $string .= var_export($val, true);
            $string .= "\n\n";
        }

        echo "<pre>";
        echo $string;
        echo "</pre>";

    }
}