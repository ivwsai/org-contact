<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * 模型基类，用于数据库操作等通用继承类
 * @package Netap
 * @category System
 * @author  OAM Team
 *
 */
class Netap_Model
{
    /**
     * Mysqli数据库实例化对象
     * @var Netap_Mysqli
     */
    protected $_db;

    public function __construct($dsn = NULL)
    {
        if (is_object($dsn)) {
            $this->_db = $dsn;
        } else {
            $this->_db = new Netap_Mysqli($dsn);
        }
    }

    /**
     * 预处理表字段
     * @param array $default
     * @param array $columns
     * @return array
     */
    protected function prepareColumns(array $default, array $columns, $flag = false)
    {

        $cols = array();
        if ($flag) $default = $columns;
        foreach ($default as $key => $val) {
            if (!array_key_exists($key, $columns)) {
                continue;
            }

            switch (gettype($columns[$key])) {
                case 'string':
                    $cols[$key] = '\'' . $this->_db->escapeString($columns[$key]) . '\'';
                    break;
                case 'integer':
                    // Convert to non-locale aware float to prevent possible commas
                    $cols[$key] = (int)$columns[$key];
                    break;
                case 'boolean':
                    $cols[$key] = (int)$columns[$key];
                    break;
                case 'double':
                    // Convert to non-locale aware float to prevent possible commas
                    $cols[$key] = sprintf('%F', $columns[$key]);
                    break;
                default:
                    $cols[$key] = ($columns[$key] === NULL) ? 'NULL' : $columns[$key];
                    break;
            }
        }

        return $cols;
    }

    /**
     * 构建插入sql
     * @param string $table
     * @param array $columns
     * @param boll $duplicate 插入时主键冲突时，启用更新行为
     * @return string|FALSE
     */
    protected function bulidInsertSql($table, array $columns, $duplicate = FALSE)
    {
        if (empty($columns)) {
            return FALSE;
        }

        $keys = $vals = array();
        foreach ($columns as $key => $val) {
            $keys[] = $key;
            $vals[] = $val;
        }
        $sql = 'INSERT INTO `' . $table . '` (`' . implode("`,`", $keys) . '`) VALUES (' . implode(",", $vals) . ')';

        if ($duplicate) {
            $valstr = array();
            foreach ($columns as $key => $val) {
                $valstr[] = "`$key`=$val";
            }
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(",", $valstr);
        }

        return $sql;
    }

    /**
     * 构建更新sql
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param int $limit 更新几条记录,默认一条
     * @return string|FALSE
     */
    protected function bulidUpdateSql($table, array $columns, array $where = array(), $limit = 1)
    {
        if (empty($columns)) {
            return FALSE;
        }

        $valstr = array();
        foreach ($columns as $key => $val) {
            $valstr[] = "`$key`=$val";
        }

        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $valstr);
        if (!empty($where)) {
            $wherestr = array();
            foreach ($where as $key => $val) {
                $wherestr[] = $key . '\'' . $this->_db->escapeString($val) . '\'';
            }
            $sql .= ' WHERE ' . implode(' AND ', $wherestr);
        }

        if ($limit !== NULL) {
            $sql .= " LIMIT " . intval($limit);
        }

        return $sql;
    }

    /**
     *
     * 插入字段SQL语句分析
     * @deprecated 不建议使用,新用法为prepareColumns、bulidInsertSql结合使用
     * @access public
     * @param mixed $data 数据
     * @param string $table 参数表达式
     * @param boolean $replace 是否replace
     * @return string
     */
    public function insert($data, $table, $replace = false)
    {
        $values = $fields = array();
        foreach ($data as $key => $val) {
            $value = $this->parseValue($val);
            if (is_scalar($value)) { // 过滤非标量数据
                $values[] = $value;
                $fields[] = $key;
            }
        }
        $sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $table . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        return $sql;
    }

    /**
     * set分析
     * @deprecated 不建议使用
     * @access public
     * @param array $data
     * @param bool $escape
     * @return string
     */
    public function parseSet($data, $escape = TRUE)
    {
        $set = array();
        foreach ($data as $key => $val) {
            $value = $this->parseValue($val, $escape);
            /* 过滤非标量数据 */
            if (is_scalar($value)) {
                $set[] = $key . '=' . $value;
            }
        }
        return ' SET ' . implode(',', $set);
    }

    /**
     * Duplicate update分析 用于insert后面接入
     * @deprecated 不建议使用
     * @access public
     * @param array $data
     * @param bool $escape
     * @return string
     */
    public function parseDupUpdate($data, $escape = TRUE)
    {
        $set = array();
        foreach ($data as $key => $val) {
            $value = $this->parseValue($val, $escape);
            /* 过滤非标量数据 */
            if (is_scalar($value)) {
                $set[] = $key . '=' . $value;
            }
        }
        return ' ' . implode(',', $set);
    }

    /**
     * value分析
     * @deprecated 不建议使用
     * @access protected
     * @param mixed $value
     * @param bool $escape
     * @return string
     */
    private function parseValue($value, $escape = TRUE)
    {
        if (is_string($value)) {
            if ($escape) {
                $value = '\'' . $this->_db->escapeString($value) . '\'';
            } else {
                $value = '\'' . $value . '\'';
            }
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            if ($escape) {
                $value = $this->_db->escapeString($value[1]);
            } else {
                $value = $value[1];
            }
        } elseif (is_array($value)) {
            $value = array_map(array($this, 'parseValue'), $value);
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }
}