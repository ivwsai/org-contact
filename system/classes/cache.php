<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * Netap_cache 处理类
 * @package Netap
 * @category System
 *
 */
class Netap_Cache
{

    /**
     * @var Object  存放缓存类型
     */
    private $cache = NULL;

    /**
     * 构造函数
     * @param array|string $config 配置文件
     * @param string $driver
     * @throws Netap_DbException
     * @throws Netap_Exception
     * @internal param string $cache_type 缓存类型filecache/memcache
     */
    public function __construct($config = 'default', $driver = '')
    {
        if (empty($config)) {
            $config = 'default';
        }

        if (is_string($config)) {
            $cfg = Netap_Config::config('cache');
            if (empty($cfg[$config])) {
                throw new Netap_DbException('缓存配置文件读取错误，请检查：' . $config);
            }
            $config = $cfg[$config];
        }

        if (empty($driver)) {
            $driver = $config['driver'];
        }

        $driver_classname = 'Netap_Cache_' . $driver;

        if (!class_exists($driver_classname)) {
            throw new Netap_Exception("未知缓存类型：" . $driver);
        }

        $this->cache = new $driver_classname($config);
    }

    /**
     * 更新缓存
     * @param string $key 主键
     * @param $value
     * @param int $lifetime 保存时间，按秒为单位
     * @return
     * @throws Netap_Exception
     * @internal param obj $data 数据，通常是数组
     */
    public function set($key, $value, $lifetime = NULL)
    {
        if (empty($this->cache)) {
            throw new Netap_Exception("未知缓存类型异常");
        }

        return $this->cache->set($key, $value, $lifetime);
    }

    /**
     * 获取缓存
     * @param string $key 主键
     * @throws Netap_Exception
     */
    public function get($key)
    {
        if (empty($this->cache)) {
            throw new Netap_Exception("未知缓存类型异常");
        }

        return $this->cache->get($key);
    }

    /**
     * 删除缓存
     * @param string $key 主键
     * @throws Netap_Exception
     */
    public function delete($key)
    {
        if (empty($this->cache)) {
            throw new Netap_Exception("未知缓存类型异常");
        }

        return $this->cache->delete($key);
    }
}

/**
 * 所有Cache都要实现次接口，才能被正确调用
 *
 */
interface Netap_ICache
{
    /**
     * 设置缓存对象
     * @param string $key 主键
     * @param $value
     * @param int $lifetime 保存时间，按秒为单位
     * @param boolean $compress 是否压缩存储，内容较大时使用
     * @return
     * @internal param obj $data 数据，通常是数组
     */
    public function set($key, $value, $lifetime = NULL, $compress = FALSE);

    /**
     * 获取缓存对象，当获取失败时，返回NULL或FALSE
     * @param string $key 主键
     * @param null $default
     * @return
     */
    public function get($key, $default = NULL);

    /**
     * 删除缓存对象
     * @param string $key 主键
     */
    public function delete($key);
}
