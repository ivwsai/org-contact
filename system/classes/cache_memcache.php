<?php defined('SYSPATH') or die('No direct script access.');

if (!interface_exists('Netap_ICache')) {
    throw new Netap_Exception('不允许直接使用Netap_Cache_memcache，请从Netap_Cache类调用!');
}

/**
 *
 * Netap_Cache_memcache 处理类，不能直接使用，只能从Netap_Cache类调用
 * @package Netap
 * @category System
 *
 */
class Netap_Cache_memcache implements Netap_ICache
{
    /**
     * @var int 超时时间(秒)
     */
    protected $lifetime = 3600;

    /**
     * @var string KEY前缀
     */
    protected $prefix = '';

    /**
     * Memcache实例
     * @var object
     */
    protected $memcache = null;

    private $is_memcached = false;

    /**
     * 构造函数
     * @param array $config 配置项
     * @throws Netap_Exception
     */
    public function __construct($config)
    {
        if (!class_exists('Memcache')) {
            throw new Netap_Exception('Memcache类不存在错误');
        }

        if (!isset($config['memcache']['servers'])) {
            throw new Netap_Exception('找不到服务器列表配置');
        }

        if (isset($config['lifetime'])) {
            $this->lifetime = intval($config['lifetime']);
        }

        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }

        if (isset($config['memcache']['driver']) && $config['memcache']['driver'] == 'memcache') {
            $this->memcache = new Memcache();
            $this->is_memcached = false;
        } else {
            $this->memcache = new Memcached();
            $this->is_memcached = true;
        }

        if (count($config['memcache']['servers']) == 1) {
            $srv = $config['memcache']['servers'][0];
            if ($this->is_memcached) {
                $this->memcache->addServer($srv['host'], $srv['port'], $srv['weight']);
            } else {
                //$this->memcache->connect($srv['host'],$srv['port'],$srv['timeout']);
                $this->memcache->addServer($srv['host'], $srv['port'], $srv['persistent'], $srv['weight'], $srv['timeout'], $srv['retry_interval']);
            }
        } else {
            foreach ($config['memcache']['servers'] as $srv) {
                if ($this->is_memcached) {
                    $this->memcache->addServer($srv['host'], $srv['port'], $srv['weight']);
                } else {
                    $this->memcache->addServer($srv['host'], $srv['port'], $srv['persistent'], $srv['weight'], $srv['timeout'], $srv['retry_interval']);
                }
            }
        }
    }

    /**
     * 更新缓存
     * @param string $key 主键
     * @param Object $data 数据，通常是数组
     * @param int $lifetime 保存时间，按秒为单位
     * @param boolean $compress 是否压缩存储，内容较大时使用
     * @return bool
     */
    public function set($key, $value, $lifetime = NULL, $compress = FALSE)
    {
        if ($lifetime === NULL) {
            $lifetime = $this->lifetime;
        }

        if ($this->is_memcached) {
            return $this->memcache->set($this->prefix . $key, $value, $lifetime);
        } else {
            return $this->memcache->set($this->prefix . $key, $value, $compress ? MEMCACHE_COMPRESSED : 0, $lifetime);
        }
    }

    /**
     * 获取缓存
     * @param string $key 主键
     * @param $default 没取到值返回的默认值
     * @param boolean $compress 是否压缩存储，内容较大时使用
     * @return mixed|null|没取到值返回的默认值
     */
    public function get($key, $default = NULL, $compress = FALSE)
    {
        if ($this->is_memcached) {
            $val = $this->memcache->get($this->prefix . $key);
        } else {
            $val = $this->memcache->get($this->prefix . $key, $compress ? MEMCACHE_COMPRESSED : 0);
        }

        return empty($val) ? $default : $val;
    }

    /**
     * 删除缓存
     * @param string $key 主键
     * @return bool
     */
    public function delete($key)
    {
        return $this->memcache->delete($this->prefix . $key);
    }
}
