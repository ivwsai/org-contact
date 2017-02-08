<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * ds用于数据库连接池管理
 * @package Netap
 * @category System
 *
 */
class Netap_Ds
{

    /**
     * @var array 狭义数据库链接池
     */
    private $dbpool = array();


    /**
     * 构造函数
     * @throws Netap_DbException
     */
    function __construct()
    {
    }

    /**
     * 获取数据链接
     * @param array $config 配置文件
     * @return Netup_Mysqli
     * @throws Netap_DbException
     */
    public function getDb($config)
    {
        if (empty($config)) {
            $config = 'default';
        }

        if (is_string($config)) {
            $cfg = Netap_Config::config('database');
            if (empty($cfg[$config])) {
                throw new Netap_DbException('数据库配置文件读取错误，请检查：' . $config);
            }
            $config = $cfg[$config];
        }

        if (!is_array($config) || !isset($config ['hostspec']) || !isset($config ['port']) || !isset($config ['database'])) {
            throw new Netap_DbException('数据库初始化错误,请检查配置参数是否正确!');
        }

        /* 根据主机、端口、数据库复用数据库链接  */
        $currkey = $config['hostspec'] . ':' . $config['port'] . ':' . $config ['database'];

        if (array_key_exists($currkey, $this->dbpool)) {
            /* 返回一个连接副本，便于调用方对数据库的保持 */
            $connection = clone $this->dbpool[$currkey];
            $connection->changeDatabase($config['database'], '', TRUE);
        } else {
            $connection = $this->dbpool[$currkey] = new Netap_Mysqli($config);
        }

        return $connection;
    }

    /**
     *  清空数据库连接池
     */
    private function purge()
    {
        $this->dbpool = array();
    }

    /**
     * 析构函数
     */
    function __destruct()
    {
    }
}
