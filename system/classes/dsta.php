<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * dsta用于单个数据库事务控制，数据库连接池管理，仅支持InnoDB数据库引擎
 * @package Netap
 * @category System
 *
 */
class Netap_DsTa
{

    /**
     * @var array 狭义数据库链接池
     */
    private $dbpool = array();


    /**
     * 构造函数
     * @param string /array $config
     * @throws Netap_DbException
     */
    function __construct()
    {
    }

    /**
     * 获取数据链接
     * @param $config
     * @return Netup_Mysqli
     * @throws Netap_DbException
     */
    public function getDb($config=array())
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

            /* 开始单数据库事务 */
            if (!$connection->query('START TRANSACTION', 'UPDATE')) {
                throw new Netap_DbException('事务开始失败:' . $connection->error());
            }
        }

        return $connection;
    }

    /**
     * 提交数据库事务，如果不调用本函数主动提交事务，系统会自动回滚当前事务
     * @return boolean
     */
    public function commit()
    {
        $result = true;

        foreach ($this->dbpool as $key => $conn) {
            Netap_Logger::info('COMMIT:' . $key);
            $proc = $conn->query('COMMIT', 'UPDATE');
            if (empty($proc)) {
                $result = $result && false;
            }
        }
        $this->purge();
        return $result;
    }

    /**
     * 回滚数据库事务
     * @return boolean
     */
    public function rollback()
    {
        $result = true;

        foreach ($this->dbpool as $key => $conn) {
            Netap_Logger::info('ROLLBACK:' . $key);
            $proc = $conn->query('ROLLBACK', 'UPDATE');
            if (empty($proc)) {
                $result = $result && false;
            }
        }
        $this->purge();
        return $result;
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
        if (count($this->dbpool) > 0) {
            $this->rollback();
        }
    }
}
