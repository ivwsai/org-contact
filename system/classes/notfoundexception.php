<?php defined('SYSPATH') or die('No direct access');

/**
 * 页面未找到异常
 */
class Netap_NotFoundException extends Exception
{

    /**
     * Set internal properties.
     *
     * @param  string  URL of page
     * @param  string  custom error template
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        parent::__construct($message ? $message : '页面没找到异常', $code, $previous);
    }

}
