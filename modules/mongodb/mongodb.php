<?php defined('SYSPATH') or die('No direct script access.');

/**
 * [UAP Server] (C)2001-2009 ND Inc.
 * $Id: mongo.class.php 126443 2011-07-05 16:50:50Z zhangyu $
 */
class Module_Mongodb
{
    public static $instances = array();
    protected $_conn;
    protected $_config;
    protected $_db_name;
    protected $_collection_name;
    protected $_db;
    protected $_collection;

    function __construct($name = 'default')
    {
        $config = Netap_Config::config('mongodb');

        $this->_config = $config[$name];
    }

    /**
     * 单例模式
     *
     * @param string $name
     * @return object
     */
    public static function &instance($name = 'default')
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }

    /**
     * 连接数据库、集合
     *
     * @param string $db_name
     * @param string $collection_name
     * @return unknown
     */
    function connect($db_name = null, $collection_name = null)
    {
        if (!empty($this->_collection)) {
            if (($db_name == $this->_db_name || empty($db_name)) && ($collection_name == $this->_collection_name || empty($collection_name)))
                return $this->_collection;
        }

        if ($this->_config['persist']) {
            $this->_conn = new Mongo('mongodb://' . $this->_config['host'] . ':' . $this->_config['port'], array('persist' => $this->_config['persist']));
        } else {
            $this->_conn = new Mongo('mongodb://' . $this->_config['host'] . ':' . $this->_config['port']);
        }

        if (!empty($db_name)) {
            $db = $this->_conn->selectDB($db_name);
        } else {
            $db = $this->_conn->selectDB($this->_config['db']);
        }

        if (!empty($collection_name)) {
            $collection = $db->selectCollection($collection_name);
        } else {
            $collection = $db->selectCollection($this->_config['collection']);
        }

        $this->_db_name = $db_name;
        $this->_collection_name = $collection_name;
        $this->_db = $db;
        $this->_collection = $collection;

        return $collection;
    }

    /**
     * 关闭数据库
     */
    public function __destruct()
    {
        if ($this->_conn) {
            $this->_conn->close();
        }
    }
}
