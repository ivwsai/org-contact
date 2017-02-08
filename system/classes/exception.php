<?php defined('SYSPATH') or die('No direct access');

/**
 *
 *  exception 异常处理类
 * @package Netap
 * @category System
 * @author OAM Team
 *
 */
class Netap_Exception extends Exception
{
    /**
     * 构造函数
     * @param string $message
     * @param int $code
     * @param array $variables
     */
    public function __construct($message, $code = 0, array $variables = NULL)
    {
        /* 设置异常消息和代码等 */
        $message = empty($variables) ? $message : strtr($message, $variables);
        parent::__construct($message, $code);

    }

    /**
     * 将异常规格化为字符串消息的方法
     */
    public function __toString()
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]', get_class($this), $this->getCode(), strip_tags($this->getMessage()), $this->getFile(), $this->getLine());
    }

}