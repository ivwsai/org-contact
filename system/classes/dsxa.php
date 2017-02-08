<?php defined('SYSPATH') or die('No direct access');

/**
 *
 *  dsxa 用于本地链接管理和XA分布式事务控制
 * @package Netap
 * @category System
 * @author  OAM Team
 *
 */
class Netap_DsXa
{

    /**
     * @var array 狭义数据库链接池
     */
    private $dbpool = array();

    /**
     * @var string 全局分布事务编号
     */
    private $xid = '';

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->begin();
    }

    /**
     * 获取数据链接
     * @param array $config 配置文件
     * @throws Netap_DbException
     * @return Netup_Mysqli
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

        if (!is_array($config) || !isset($config ['hostspec']) || !isset($config ['port'])) {
            throw new Netap_DbException('数据库初始化错误,请检查配置参数是否正确!');
        }

        /* 根据主机、端口、数据库复用数据库链接  */
        $currkey = $config['hostspec'] . ':' . $config['port'];

        if (array_key_exists($currkey, $this->dbpool)) {

            /* 返回一个连接副本，便于调用方对数据库的保持 */
            $connection = clone $this->dbpool[$currkey];
            $connection->changeDatabase($config['database'], '', TRUE);

        } else {
            $this->dbpool[$currkey] = $connection = new Netap_Mysqli($config);

            $xid = $this->xid;
            if (!empty($xid)) {

                /* 开始XA事务 */
                $connection->query("XA BEGIN '$xid'", "UPDATE");
            }
        }

        return $connection;
    }

    /**
     * 提交分布式事务
     * @return boolean
     */
    public function commit()
    {
        if (!$this->prepare()) {
            $this->rollback();
            return false;
        }

        $result = true;
        $xid = $this->xid;

        foreach ($this->dbpool as $conn) {
            $proc = $conn->query("XA COMMIT '$xid'", "UPDATE");
            if (empty($proc)) {
                $result = false;
            }
        }

        //TODO: 客户端重试保证事务完整性或写入日志

        $this->reset();

        return $result;
    }

    /**
     * 回滚分布式事务
     * @return boolean
     */
    public function rollback()
    {
        $result = true;
        $xid = $this->xid;

        //放入IDLE状态
        foreach ($this->dbpool as $conn) {
            $proc = $conn->query("XA END '$xid'", "UPDATE");
            if (empty($proc)) {
                $result = false;
                break;
            }
        }

        foreach ($this->dbpool as $conn) {
            $proc = $conn->query("XA ROLLBACK '$xid'", "UPDATE");
            if (empty($proc)) {
                $result = false;
            }
        }

        $this->reset();

        return $result;
    }

    /**
     * 开始分布式事务
     * @param string $uniqid
     */
    private function begin($uniqid = '')
    {
        if (empty($uniqid))
            $this->xid = uniqid();
        else
            $this->xid = $uniqid;
        $this->dbpool = array();
    }

    /**
     * 预提交分布式事务
     * @return boolean
     */
    private function prepare()
    {
        $prepare = true;
        $xid = $this->xid;

        /* 放入IDLE状态 */
        foreach ($this->dbpool as $conn) {
            $proc = $conn->query("XA END '$xid'", "UPDATE");

            if (empty($proc)) {
                $prepare = false;
                break;
            }
        }
        /* 放入PREPARED状态 */
        foreach ($this->dbpool as $conn) {
            $proc = $conn->query("XA PREPARE '$xid'", "UPDATE");
            if (empty($proc)) {
                $prepare = false;
                break;
            }
        }
        return $prepare;
    }

    /**
     * 重置事务及链接池
     */
    private function reset()
    {
        $this->xid = '';
        $this->dbpool = array();
    }

    /**
     * 析构函数
     */
    function __destruct()
    {
        if (!empty($this->xid)) {
            $this->rollback();
        }
    }
}
