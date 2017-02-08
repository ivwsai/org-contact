<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Sphinx搜索引擎工具类
 *
 * @package    Netap
 * @category   Module
 * @copyright Copyright 2014-2014 © 91.com All rights reserved.
 */
require_once MODPATH . '/sphinx/sphinxapi.php';

class Module_Sphinx
{

    /**
     * @access private
     * @var SphinxClient Sphinx客户端
     */
    private $client;

    /**
     * @access private
     * @var string Sphinx索引名称
     */
    private $index = "";

    /**
     * @access private
     * @var array Sphinx客户端(搜索)选项
     */
    private $options = "";

    // sphinx instances
    protected static $_instance = array();

    /**
     * Singleton pattern
     *
     * @return Module_Sphinx
     */
    public static function instance($kind = 'default')
    {
        if (!isset(Module_Sphinx::$_instance[$kind])) {
            // Load the configuration for this type
            $config = Netap_Config::config('sphinx');

            // Create a new session instance
            Module_Sphinx::$_instance[$kind] = new Module_Sphinx($config, $kind);
        }

        return Module_Sphinx::$_instance[$kind];
    }

    /**
     * 根据配置参数构造Sphinx客户端连接
     * @param array $config
     * @param string $kind
     * @throws Netap_Exception
     */
    public function  __construct($config = array(), $kind = 'default')
    {
        if (empty($config)) {
            $config = Netap_Config::config('sphinx');
            $config = isset($config[$kind]) ? $config[$kind] : array();
        }

        if (!isset($config["host"]) || empty($config["host"])) {
            throw new Netap_Exception("Sphinx服务IP地址不能为空错误，请检查配置文件!");
        }
        if (!isset($config["port"]) || empty($config["port"])) {
            throw new Netap_Exception("Sphinx服务端口不能为空错误，请检查配置文件!");
        }
        if (!isset($config["index"]) || empty($config["index"])) {
            throw new Netap_Exception("Sphinx索引名称不能为空错误，请检查配置文件!");
        }

        $timeout = 1;
        if (isset($config["timeout"])) {
            $timeout = intval($config["timeout"]);
        }

        $this->client = new SphinxClient();
        $this->client->setServer($config["host"], intval($config["port"]));
        $this->client->SetConnectTimeout($timeout);
        $this->index = $config["index"];

        $this->options = array(
            'before_match' => '<b style="color:red">',
            'after_match' => '</b>',
            'chunk_separator' => '...',
            'limit' => 60,
            'around' => 3,
        );

        if (isset($config["options"])) {
            $this->options = $config["options"];
        }
    }

    /**
     * 获取分词结果
     * @param string $query 抽取关键字的目标字符串
     * @param bool $hits 指定了是否需要返回关键词出现此处的信息
     * @return date
     */
    public function BuildKeywords($query, $hits = false)
    {
        return $this->client->BuildKeywords($query, $this->index, $hits);
    }

    /**
     * 产生文本摘要和高亮
     * @param array $docs 包含各文档内容的数组
     * @param string $words 包含需要高亮的关键字的字符串
     * @return array 返回包含有片段（摘要）字符串的数组
     * @return bool 失败时返回false
     */
    public function BuildExcerpts($docs, $words)
    {
        return $this->client->BuildExcerpts($docs, $this->index, $words, $this->options);
    }

    /**
     * 设置timeout
     * @param int $timeout 连接超时时间
     */
    public function setTimeout($timeout)
    {
        $this->client->SetConnectTimeout($timeout);
    }

    /**
     * 获取SphinxClient
     * @return SphinxClient 返回Sphinx客户端对象
     */
    public function getClient()
    {
        return $this->client;
    }
}
