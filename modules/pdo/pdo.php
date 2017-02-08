<?php defined('SYSPATH') or die('No direct script access.');

class Module_Connection extends PDO
{
    private $key = null;
    private $driver = null;

    public function __construct($key, $driver, $host, $port, $database,
                                $username, $password, $options)
    {
        $dsn = "$driver:host=$host;port=$port;dbname=$database";
        parent::__construct($dsn, $username, $password, $options);
        $this->key = $key;
        $this->driver = $driver;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function prepareAndBind($sql, $params, $options = array())
    {
        $statement = parent::prepare($sql, $options);
        if (empty($params)) {
            return $statement;
        }

        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }

        return $statement;
    }

    public function fetch($sql, $params = null, $options = array())
    {
        $statement = $this->prepareAndBind($sql, $params, $options);
        if (is_null($statement)) {
            return null;
        }

        $statement->execute();
        $object = $statement->fetch(PDO::FETCH_OBJ);
        return empty($object) ? null : $object;
    }

    public function query($sql, $params = null, $options = array())
    {
        $statement = $this->prepareAndBind($sql, $params, $options);
        if (is_null($statement)) {
            return null;
        }

        return new Module_Cursor($statement);
    }

    public function queryWithTotal($sql, $params = null, $options = array())
    {
        if ($this->driver !== 'mysql') {
            return null;
        }

        $sql = str_replace('select ', 'select SQL_CALC_FOUND_ROWS ', $sql);
        $cursor = $this->query($sql, $params, $options);
        if (is_null($cursor)) {
            return null;
        }

        $cursor->execute();
        $sql = 'select found_rows() as total';
        $result = $this->fetch($sql);
        if (is_null($result)) {
            return null;
        }

        $list = new stdClass();
        $list->total = intval($result->total);
        $list->cursor = $cursor;

        return $list;
    }

    public function safeExecute($sql, $params = null, $options = array())
    {
        $statement = $this->prepareAndBind($sql, $params, $options);
        if (is_null($statement)) {
            return -1;
        }

        $this->beginTransaction();
        try {
            $statement->execute();
            $this->commit();
            return $statement->rowCount();
        } catch (PDOException $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function execute($sql, $params = null, $options = array())
    {
        $statement = $this->prepareAndBind($sql, $params, $options);
        if (is_null($statement)) {
            return -1;
        }

        $statement->execute();
        return $statement->rowCount();
    }
}


class Module_Statement
{
    private $statement = null;
    private $connection = null;
    private $dbname = null;

    public function __construct($connection, $statement, $dbname = null)
    {
        $this->connection = $connection;
        $this->statement = $statement;
        $this->dbname = $dbname;
    }

    public function execute()
    {
        if (!empty($this->dbname)) {
            $this->connection->exec("use $this->dbname");
        }

        return $this->statement->execute();
    }

    public function fetch()
    {
        return $this->statement->fetch(PDO::FETCH_OBJ);
    }

    public function closeCursor()
    {
        return $this->statement->closeCursor();
    }
}

class Module_Transaction
{
    private $connections = array();

    public function begin($connection)
    {
        $key = $connection->getKey();
        if (array_key_exists($key, $this->connections)) {
            return false;
        }

        $connection->beginTransaction();
        $this->connections[$key] = $connection;
        return true;
    }

    public function commit()
    {
        foreach ($this->connections as $connection) {
            if (!$connection->commit()) {
                return false;
            }
        }

        return true;
    }

    public function rollBack()
    {
        foreach ($this->connections as $connection) {
            $connection->rollBack();
        }
    }
}

class Module_Cursor implements Iterator
{
    private $index = -1;
    private $position = -1;
    private $object = null;
    private $statements = array();

    public function __construct($statement)
    {
        $this->add($statement);
    }

    public function add($statement)
    {
        if (!is_null($statement)) {
            $this->statements[] = $statement;
        }
    }

    private function fetch()
    {
        if ($this->index < 0 || $this->position < 0) {
            throw new PDOException('invalid state');
        }

        for (; $this->index < count($this->statements); $this->index++) {
            $this->object = $this->statements[$this->index]->fetch(PDO::FETCH_OBJ);
            if (!empty($this->object)) {
                $this->position++;
                return;
            }
        }
    }

    public function current()
    {
        return $this->object;
    }

    public function next()
    {
        $this->fetch();
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return $this->index < 0 || $this->position < 0 || !empty($this->object);
    }

    public function execute()
    {
        foreach ($this->statements as $statement) {
            $statement->closeCursor();
            $statement->execute();
        }

        $this->index = 0;
        $this->position = 0;
    }

    public function &toArray()
    {
        $list = iterator_to_array($this);
        $data = array_values($list);
        return $data;
    }

    public function rewind()
    {
        if ($this->position != 0 || $this->index != 0) {
            $this->execute();
        }

        $this->fetch();
    }
}

class Module_Pdo
{
    private $connections = array();
    private $config = null;
    private $transaction = null;

    public static function bindPdo($sql, $obj)
    {
        if (is_null($obj)) {
            return null;
        }

        $fields =& $obj;
        if (is_object($obj)) {
            $fields = get_object_vars($obj);
        }

        $keys = join(',', array_keys($fields));
        $vars = ':' . join(',:', array_keys($fields));

        $sql = str_replace('<keys>', $keys, $sql);
        $sql = str_replace('<vars>', $vars, $sql);

        return $sql;
    }

    public function dump()
    {
        $info = array(
            'config' => &$this->config,
            'connections' => &$this->connections,
            'transaction' => &$this->transaction,
        );
        return print_r($info, true);
    }

    public function __construct($config = 'pdo')
    {
        if (is_string($config)) {
            $config = Netap_Config::config($config);
        }

        if (is_array($config)) {
            $this->config = $config;
        }
    }

    private function getConnection(&$config)
    {
        $key = array_key_exists('key', $config) ? $config['key'] : null;
        if (empty($key)) {
            $driver = array_key_exists('driver', $config)
                ? strtolower(trim($config['driver'])) : 'mysql';
            $host = array_key_exists('hostspec', $config)
                ? trim($config['hostspec']) : 'localhost';
            $port = array_key_exists('port', $config)
                ? intval($config['port']) : null;
            $database = array_key_exists('database', $config)
                ? trim($config['database']) : null;
            $username = array_key_exists('username', $config)
                ? trim($config['username']) : null;
            $password = array_key_exists('password', $config)
                ? trim($config['password']) : null;
            $options = array_key_exists('options', $config)
                ? $config['options'] : array(PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8');

            if (is_array($options)) {
                ksort($options);
            }

            $key = md5($driver . $host . $port . $username
                . ($driver != 'mysql' ? $database : null)
                . serialize($options));
            $config['key'] = $key;
        }

        if (!array_key_exists($key, $this->connections)) {
            $connection = new Module_Connection($key, $driver, $host, $port,
                $database, $username, $password, $options);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connections[$key] = $connection;
        }

        return $this->connections[$key];
    }

    public function getConnectionByParam($sql, $params)
    {
        $dbname = null;
        $config =& $this->config;
        if (array_key_exists('sharder', $this->config)) {
            $shard = call_user_func(
                $this->config['sharder'], $sql, (array)$params);
            if (is_null($shard) ||
                (!array_key_exists('index', $shard) &&
                    !array_key_exists('dbname', $shard)) ||
                (is_null($shard['index']) && is_null($shard['dbname']))
            ) {
                return null;
            }

            if (array_key_exists('shards', $this->config)) {
                $index = null;
                if (array_key_exists('index', $shard)) {
                    $index = $shard['index'];
                }
                $config =& $this->config['shards'][$index];
            }

            if (array_key_exists('dbname', $shard)) {
                $dbname = $shard['dbname'];
            } else {
                $dbname = array_key_exists('database', $config) ? $config['database'] : null;
            }
        }

        $connection = $this->getConnection($config);
        if (!is_null($connection) &&
            (!array_key_exists('driver', $config) ||
                strtolower($config['driver']) === 'mysql') &&
            !empty($dbname)
        ) {
            $connection->exec("use $dbname");
        }

        return $connection;
    }

    public function fetch($sql, $params = null, $options = array())
    {
        $connection = $this->getConnectionByParam($sql, $params);
        return is_null($connection) ?
            $this->batchFetch($sql, $params, $options) :
            $connection->fetch($sql, $params, $options);
    }

    public function query($sql, $params = null, $options = array())
    {
        $connection = $this->getConnectionByParam($sql, $params);
        return is_null($connection) ?
            $this->batchQuery($sql, $params, $options) :
            $connection->query($sql, $params, $options);
    }

    public function queryWithTotal($sql, $params = null, $options = array())
    {
        if (array_key_exists('driver', $this->config) &&
            strtolower($this->config['driver']) !== 'mysql'
        ) {
            throw new Exception('only supported by mysql');
        }

        $connection = $this->getConnectionByParam($sql, $params);
        if (is_null($connection)) {
            return null;
        }

        return $connection->queryWithTotal($sql, $params, $options);
    }

    public function execute($sql, $params = null, $options = array())
    {
        $connection = $this->getConnectionByParam($sql, $params);
        if (!is_null($this->transaction) && !is_null($connection)) {
            $this->transaction->begin($connection);
        }
        return is_null($connection) ?
            $this->batchExecute($sql, $params, $options) :
            $connection->execute($sql, $params, $options);
    }

    public function safeExecute($sql, $params = null, $options = array())
    {
        $rowCount = 0;
        $this->beginTransaction();

        try {
            $rowCount = $this->execute($sql, $params, $options);
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        $this->commit();
        return $rowCount;
    }

    public function beginTransaction()
    {
        if (!is_null($this->transaction)) {
            throw new Exception('nested transaction not supported');
        }

        $this->transaction = new Module_Transaction();
    }

    public function commit()
    {
        if (is_null($this->transaction)) {
            throw new Exception('no transaction yet');
        }

        $this->transaction->commit();
        $this->transaction = null;
    }

    public function rollBack()
    {
        if (is_null($this->transaction)) {
            throw new Exception('no transaction yet');
        }

        $this->transaction->rollBack();
        $this->transaction = null;
    }

    public function batchExecute($sql, $params = null, $options = array())
    {
        $rowCount = 0;

        if (array_key_exists('shards', $this->config)) {
            foreach ($this->config['shards'] as &$shard) {
                $connection = $this->getConnection($shard);
                if (!is_null($this->transaction)) {
                    $this->transaction->begin($connection);
                }
                $rowCount += $connection->execute($sql, $params, $options);
            }
        } elseif (!array_key_exists('driver', $this->config)
            || strtolower($this->config['driver']) === 'mysql'
        ) {
            $connection = $this->getConnection($this->config);
            if (!is_null($this->transaction)) {
                $this->transaction->begin($connection);
            }

            foreach ($this->config['dbnames'] as $dbname) {
                $connection->exec("use $dbname");
                $rowCount += $connection->execute($sql, $params, $options);
            }
        }

        return $rowCount;
    }

    public function batchQuery($sql, $params = null, $options = array())
    {
        $cursor = new Module_Cursor();
        if (array_key_exists('shards', $this->config)) {
            foreach ($this->config['shards'] as &$shard) {
                $connection = $this->getConnection($shard);
                $cursor->add(new Module_Statement($connection,
                    $connection->prepareAndBind($sql, $params, $options)));
            }
        } elseif (!array_key_exists('driver', $this->config)
            || strtolower($this->config['driver']) === 'mysql'
        ) {
            $connection = $this->getConnection($this->config);
            foreach ($this->config['dbnames'] as $dbname) {
                $connection->exec("use $dbname");
                $cursor->add(new Module_Statement($connection,
                    $connection->prepareAndBind($sql, $params, $options),
                    $dbname));
            }
        } else {
            return null;
        }

        return $cursor;
    }

    public function batchFetch($sql, $params = null, $options = array())
    {
        if (array_key_exists('shards', $this->config)) {
            foreach ($this->config['shards'] as &$shard) {
                $connection = $this->getConnection($shard);
                $obj = $connection->fetch($sql, $params, $options);
                if (!empty($obj)) {
                    return $obj;
                }
            }
        } elseif (!array_key_exists('driver', $this->config)
            || strtolower($this->config['driver']) === 'mysql'
        ) {
            $connection = $this->getConnection($this->config);
            foreach ($this->config['dbnames'] as $dbname) {
                $connection->exec("use $dbname");
                $obj = $connection->fetch($sql, $params, $options);
                if (!empty($obj)) {
                    return $obj;
                }
            }
        } else {
            return null;
        }

        return null;
    }
}
