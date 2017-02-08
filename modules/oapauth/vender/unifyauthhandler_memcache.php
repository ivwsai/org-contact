<?php
require_once 'unifyauthhandler.php';

/**
 * 统一会话保存,File实现
 *
 */
class UnifyAuthHandler_Memcache implements UnifyAuthHandler
{
    private $lifetime = 3600;
    private $totalweight;
    private $server_list;
    private $save_handler;
    private $sid_server;  //保存最后一次请求的sid对应的server

    public function __construct($config)
    {
        $this->lifetime = $config['lifetime'];
        $this->totalweight = 0;
        $this->save_handler = $config['save_handler'];
        $this->server_list = array();
        $this->sid = NULL;
        $this->sid_server = array();

        if ($this->save_handler == 1) {
            if (!extension_loaded('memcached')) {
                throw new Exception('PHP_Memcached extension not loaded,please check!');
            }
        } else if ($this->save_handler == 2) {
            if (!extension_loaded('memcache')) {
                throw new Exception('PHP_Memcache extension not loaded,please check!');
            }
        } else {
            $this->fatalHandlerError();
        }

        foreach ($config['save_path'] as $path) {
            list($host, $port) = explode(":", $path['path']);
            $memcache = array('host' => $host, 'port' => $port, 'weight' => $path['weight'], 'handler' => NULL);
            array_push($this->server_list, $memcache);
            $this->totalweight += $path['weight'];
        }
    }

    /**
     * 读会话数据
     */
    public function read($sid)
    {
        if ($this->save_handler == 1) {
            $handler = $this->getHandlerMemcached($this->getSidServer($sid));
            $data = $handler->get($sid);
        } else if ($this->save_handler == 2) {
            $handler = $this->getHandlerMemcache($this->getSidServer($sid));
            $data = $handler->get($sid, 0);
        } else {
            $this->fatalHandlerError();
        }

        return $data;
    }

    /**
     * 写会话数据
     */
    public function write($sid, $data)
    {
        if ($this->save_handler == 1) {
            $handler = $this->getHandlerMemcached($this->getSidServer($sid));
            return $handler->set($sid, $data, $this->lifetime);
        } else if ($this->save_handler == 2) {
            $handler = $this->getHandlerMemcache($this->getSidServer($sid));
            return $handler->set($sid, $data, 0, $this->lifetime);
        } else {
            $this->fatalHandlerError();
        }
    }

    /**
     * 销毁当前会话
     */
    public function destroy($sid)
    {
        if ($this->save_handler == 1) {
            $handler = $this->getHandlerMemcached($this->getSidServer($sid));
        } else if ($this->save_handler == 2) {
            $handler = $this->getHandlerMemcache($this->getSidServer($sid));
        } else {
            $this->fatalHandlerError();
        }
        return $handler->delete($sid);
    }

    /**
     * 垃圾回收，memcache无法进行垃圾回收，系自动回收
     */
    public function gc($lifetime)
    {
        return TRUE;
    }

    /**
     * 垃圾回收，memcache无法进行垃圾回收，系自动回收
     */
    public function close()
    {
        //关闭memcache组
        foreach ($this->server_list as $server) {
            if (!empty($server['hander'])) {
                if ($this->save_handler == 1) {
                    $server['hander']->quit();
                } else if ($this->save_handler == 2) {
                    $server['hander']->close();
                } else {
                    $this->fatalHandlerError();
                }
                $server['hander'] = NULL;
            }
        }
        return TRUE;
    }

    /**
     * 关闭memcache连接
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * 计算Sid保存的会话服务路径
     * @param string $sid
     * @throws Exception
     * @return array
     */
    private function &getSidServer($sid)
    {
        if (!empty($this->sid_server['sid']) && $this->sid_server['sid'] == $sid && !empty($this->sid_server['server'])) {
            return $this->sid_server['server'];
        }

        if ($this->totalweight < 1) {
            throw new Exception('配置读取错误');
        }
        if ($this->totalweight == 1) {
            return $this->server_list[0];
        }

        //当前只支持CRC32
        $sid_checksum = sprintf("%u", crc32($sid));
        $sid_size = strlen($sid_checksum);
        $checksum = substr($sid_checksum, ($sid_size > 9) ? ($sid_size - 9) : 0);
        $sel_checksum = (intval($checksum) % $this->totalweight) + 1;

        $curweight = 0;
        $server = NULL;
        foreach ($this->server_list as $val) {
            $curweight += $val['weight'];
            if ($curweight >= $sel_checksum) {
                $server = &$val;
                break;
            }
        }

        if (empty($server)) {
            //如果取到的路径为空，则异常
            throw new Exception('Unknow save_path error!');
        }

        $this->sid_server['sid'] = $sid;
        $this->sid_server['server'] = &$server;
        return $server;
    }

    /**
     * 获取Memcache实例
     */
    private function getHandlerMemcached(&$server)
    {
        if (empty($server['handler'])) {
            $server['handler'] = new Memcached();
            $server['handler']->addServer($server['host'], $server['port'], 1);
        }
        return $server['handler'];
    }

    /**
     * 获取Memcache实例
     */
    private function getHandlerMemcache(&$server)
    {
        if (empty($server['handler'])) {
            $server['handler'] = new Memcache();
            $server['handler']->addServer($server['host'], $server['port']);
        }
        return $server['handler'];
    }

    /**
     * 未知Memcache类型处理异常
     * @throws Exception
     */
    private function fatalHandlerError()
    {
        throw new Exception('Unknow memcache save_handler error!');
    }

}