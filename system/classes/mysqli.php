<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * MySQL数据库操作类
 *
 * @package Netap
 * @category System
 * @author OAM Team
 *
 */
class Netap_Mysqli
{

    /**
     * 数据库配置
     *
     * @var array
     */
    private $config;

    /**
     * 连接句柄
     *
     * @var resource
     */
    private $link;

    /**
     * 初始数据库名称
     *
     * @var string
     */
    private $database;

    /**
     * 客户端(连接\校验)字符集
     *
     * @var string
     */
    private $charset;

    /**
     * 当前数据库名称（全局，用于进行数据库切换的判断）
     *
     * @var string
     */
    static $real_database = '';

    /**
     *
     * @param array $config
     * @throws Netap_DbException
     */
    public function __construct($config)
    {
        if (empty($config)) {
            $config = 'default';
        }

        if (is_string($config)) {
            $cfg = Netap_Config::config('database');
            if (!is_array($cfg)) {
                throw new Netap_DbException("数据库配置文件读取错误，请检查：" . $config);
            }
            $config = $cfg[$config];
        }

        if (!is_array($config) || !isset($config["hostspec"]) || !isset($config["port"])) {
            throw new Netap_DbException("数据库初始化错误,请检查配置参数是否正确!");
        }

        /* 记录当前数据库 */
        self::$real_database = $this->database = $config['database'];
        $this->config = $config;
        $this->charset = isset($config['charset']) ? $config['charset'] : null;

        $this->link = new mysqli($config["hostspec"], $config["username"], $config["password"], $config['database'], $config["port"]);
        if ($this->link->connect_error) {
            $this->halt("数据库连接失败 :errorno=" . $this->link->connect_errno . ',error=' . $this->link->connect_error);
        } else {
            if ($this->charset) {
                $this->link->query("SET NAMES {$this->charset}");
            }
        }
    }

    /**
     * 对SQL中的特殊字符转义
     *
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        return $this->link->real_escape_string($str);
    }

    /**
     * 返回当前数据库链接配置
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 查询首条记录
     *
     * @param string $sql
     */
    public function fetch_first($sql)
    {
        $result = $this->simpquery($sql);
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    /**
     * 使用prepare的方式进行操作。无法支持muti的查询，不方便直接更改原来的函数
     * 所以另外提供了这个函数
     *
     * @author 欧远宁
     * @param string $sql 执行的SQL
     * @param array $para 需要传递的参数
     * @param string $type 执行类型，包括SELECT,UPDATE,INSERT,DELETE,XA
     * @param string $id 对返回的数据库结果集，添加以$id列值的数组索引
     * @return any 如果是insert返回insert_id，如果select返回查询到的数据，其他无返回值
     */
    public function prepare($sql, $para, $type = "SELECT", $id = '')
    {
        $type = in_array($type, array(
            "SELECT",
            "UPDATE",
            "INSERT",
            "DELETE",
            "XA"
        )) ? $type : "SELECT";

        if (!$stmt = $this->link->prepare($sql)) {
            $this->halt('MySQLi Prepare Query Error', $sql);
        }

        /* 得到绑定prepare的参数 */
        $bindPara = array();
        $kind = '';
        foreach ($para as $k => $v) {
            $_t = gettype($v);
            if ($_t == 'integer') {
                $kind .= 'i';
            } elseif ($_t == 'double') {
                $kind .= 'd';
            } else {
                $kind .= 's';
            }
            $bindPara[$k] = &$para[$k];
        }
        array_unshift($bindPara, $kind);
        /* 执行绑定，并执行stmt */
        call_user_func_array(array(
            $stmt,
            "bind_param"
        ), $bindPara);
        $stmt->execute();

        /* 根据执行类型得到返回结果 */
        if ($type == 'INSERT') {
            $insertId = $stmt->insert_id;
            $this->link->close();
            return $insertId;
        } else if ($type == 'SELECT') {
            $arr = array();
            $result = $stmt->get_result();
            while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
                $id ? $arr[$data[$id]] = $data : $arr[] = $data;
            }
            return $arr;
        }
        return null;
    }

    /**
     * 查询所有记录
     *
     * @param string $sql
     * @param string $id
     * @param boolean $multi 是否多语句查询，用于一次性执行多条SQL语句
     * @return array
     */
    public function fetch_all($sql, $id = '', $multi = FALSE)
    {
        $arr = array();
        if ($multi) {
            if ($this->multiquery($sql)) {
                do {
                    /* 数据结果集 */
                    if ($result = $this->link->store_result()) {
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                            $id ? $arr[$row[$id]] = $row : $arr[] = $row;
                        }
                        $result->free_result();
                    }
                    /* 判断还有没有结果集 */
                    if (!$this->link->more_results()) {
                        break;
                    }
                } while ($this->link->next_result());
            }
        } else {
            $result = $this->simpquery($sql);
            while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
                $id ? $arr[$data[$id]] = $data : $arr[] = $data;
            }
        }
        return $arr;
    }

    public function fetch_all2($sql, $id = '', $multi = FALSE)
    {
        $arr = array();
        if ($multi) {
            if ($this->multiquery($sql)) {
                $i = 0;
                do {
                    /* 数据结果集 */
                    if ($result = $this->link->store_result()) {
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                            $id ? $arr[$i][$row[$id]] = $row : $arr[$i][] = $row;
                        }
                        $result->free_result();
                        $i++;
                    }
                    /* 判断还有没有结果集 */
                    if (!$this->link->more_results()) {
                        break;
                    }
                } while ($this->link->next_result());
            }
        } else {
            $result = $this->simpquery($sql);
            while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
                $id ? $arr[$data[$id]] = $data : $arr[] = $data;
            }
        }
        return $arr;
    }

    /**
     * 通用SQL查询
     *
     * @param string $sql
     * @param string $type SELECT/UPDATE/INSERT/DELETE
     * @param boolean $multi 是否多语句查询，用于一次性执行多条SQL语句
     * @param string $id
     * @return array|object|query
     */
    public function query($sql, $type = "SELECT", $multi = FALSE, $id = '')
    {
        $type = in_array($type, array(
            "SELECT",
            "UPDATE",
            "INSERT",
            "DELETE",
            "XA"
        )) ? $type : "SELECT";

        if ($type == 'UPDATE' || $type == 'DELETE' || $type == 'XA') {
            if ($multi) {
                $result = $this->multiquery($sql);

                /* 修复多个时事务Commands out of sync; you can't run this command now */
                while ($this->link->more_results() && $this->link->next_result()) ;

                return $result;
            } else {
                return $this->simpquery($sql);
            }
        } else if ($type == 'INSERT') {
            return $this->simpquery($sql);
        }

        return $this->fetch_all($sql, $id, $multi);
    }

    /**
     * 查询影响了多少条记录
     */
    public function affected_rows()
    {
        return $this->link->affected_rows;
    }

    /**
     * 查询最后一次发生的错误
     */
    public function error()
    {
        return ($this->link ? $this->link->error : mysqli_error());
    }

    /**
     * 查询最后一次发生的错误代码
     */
    public function errno()
    {
        return intval($this->link ? $this->link->errno : mysqli_errno());
    }

    /**
     * 获取最后一次插入的自增Id
     */
    public function insert_id()
    {
        return $this->link->insert_id;
    }

    /**
     * 获取当前数据库版本
     */
    public function version()
    {
        return $this->link->server_info;
    }

    /**
     * 关闭当前数据库
     */
    public function close()
    {
        return $this->link->close();
    }

    /**
     * 获取当前数据库名
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * 改变当前数据库
     *
     * @param string $database
     * @param string $charset 可为空则不更改字符集
     * @return bool
     */
    public function changeDatabase($database, $charset = '', $always = FALSE)
    {
        if (self::$real_database != $database || $always) {
            if (!$this->link->select_db($database)) {
                $this->halt("数据库切换失败 :host=" . $this->link . ",dbname=" . $database);
            } else {
                self::$real_database = $this->database = $database;
            }

            /* 更改字符集 */
            if (!empty($charset)) {
                $this->setCharset($charset);
            }
        }
        return true;
    }

    /**
     * 获取当前字符集
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * 设置字符集
     *
     * @param string $charset
     */
    public function setCharset($charset)
    {
        if ($this->$charset != $charset) {
            $this->link->query("SET NAMES {$charset}");
            $this->charset = $charset;
        }
    }

    /**
     * 简单单条SQL查询
     *
     * @param string $sql
     * @param string $type
     * @param boolean $cachetime
     * @return query
     */
    public function simpquery($sql, $type = '', $cachetime = FALSE)
    {
        $this->resetdatabase();
        if (!($query = $this->link->query($sql)) && $type != 'SILENT') {
            $this->halt('MySQLi Query Error', $sql);
        }
        return $query;
    }

    /**
     * 一次执行多条SQL语句
     *
     * @param string $sql 需要执行的SQL语句
     * @return object 查询结果
     */
    public function multiquery($sql)
    {
        $this->resetdatabase();
        if (!($query = $this->link->multi_query($sql))) {
            $this->halt('MySQLi Multi Query Error', $sql);
        }
        return $query;
    }

    /**
     * 多连接环境下检查数据库是否当前数据库
     */
    private function resetdatabase()
    {
        if ($this->database != self::$real_database) {
            $this->changeDatabase($this->database);
        }
    }

    /**
     * 报告错误
     *
     * @param string $message
     * @param string $sql
     * @param int $privage
     * @throws Netap_DbException
     */
    private function halt($message = '', $sql = '', $privage = 1)
    {
        $message = "[" . $message . "] " . $this->error();
        if (defined(IS_DEBUG) && IS_DEBUG) {
            $message .= " -- " . $sql;
        }
        throw new Netap_DbException($message, $this->errno());
    }

    public function __destruct()
    {
        if (is_resource($this->link)) {
            $this->link->close();
        }
    }
}
