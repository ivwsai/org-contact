<?php
require_once 'unifyauthhandler.php';

/**
 * 统一会话保存
 *
 */
class UnifyAuth
{
    const TIME_KEY = 'TIME';
    const DATA_KEY = 'DATA';

    private $sid;            //当前会话编号 128bits
    private $sid_function = 0;
    private $lifetime;
    private $limitsize;
    private $session_cookie;
    private $writeflag;  //写会话状态,必须session_start后才能写

    private $cookie_sets; //更新cookie的列表，在结束时统一设置

    private $handler;
    public $session = array();

    public function __construct($config = NULL)
    {
        if (!is_array($config)) {
            $config = @include_once 'unifyauth.cfg.php';
        }
        if (!is_array($config)) {
            throw new Exception('找不到配置文件错误');
        }

        $this->sid_function = isset($config['sid_function']) ? $config['sid_function'] : 0;
        $this->lifetime = isset($config['lifetime']) ? intval($config['lifetime']) : (64 * 1024);
        $this->session_cookie = isset($config['session_cookie']) ? $config['session_cookie'] : array();
        $this->limitsize = isset($config['limitsize']) ? intval($config['limitsize']) : (64 * 1024);
        $this->cookie_sets = array();  //回写cookie清空
        $this->writeflag = FALSE;

        if ($config['save_handler'] == 1 || $config['save_handler'] == 2) {
            require_once 'unifyauthhandler_memcache.php';
            $this->setHandler(new UnifyAuthHandler_Memcache($config));
        } elseif ($config['save_handler'] == 0) {
            require_once 'unifyauthhandler_file.php';
            $this->setHandler(new UnifyAuthHandler_File($config));
        } else {
            throw new Exception('save_handler not supported, please check!');
        }
    }

    /**
     * 设置或获取新的会话编号
     * @param string $sid
     * @return string
     */
    public function session_id($sid = NULL)
    {
        if (!empty($sid)) {
            $this->sid = $sid;
        }
        return $this->sid;
    }

    /**
     * 开启会话
     */
    public function session_start($sid = NULL)
    {
        if ($this->writeflag == TRUE) {
            throw new Exception('Session already started,please call session_write_close()!');
        }

        if (!empty($sid)) {
            $this->session_id($sid);
        }

        if (empty($this->sid)) {
            if (!empty($this->session_cookie['cookie_enabled']) && $this->session_cookie['cookie_enabled'] == 1) {
                if (!empty($this->session_cookie['cookie_name']) && !empty($_COOKIE[$this->session_cookie['cookie_name']])) {
                    $this->sid = $_COOKIE[$this->session_cookie['cookie_name']];
                }
                if (empty($this->sid) && !empty($this->session_cookie['cookie_name_compatible']) && !empty($_COOKIE[$this->session_cookie['cookie_name_compatible']])) {
                    $this->sid = $_COOKIE[$this->session_cookie['cookie_name_compatible']];
                }
            }
        }

        if (empty($this->sid)) {
            $this->session_id($this->generateSid());
        }

        if (!empty($this->session_cookie['cookie_enabled']) && $this->session_cookie['cookie_enabled'] == 1) {
            if (!empty($this->session_cookie['cookie_name'])) {
                $this->addCookie($this->session_cookie['cookie_name'], $this->sid, NULL);
            }
            if (!empty($this->session_cookie['cookie_name_compatible'])) {
                $this->addCookie($this->session_cookie['cookie_name_compatible'], $this->sid, NULL);
            }
        }

        $data_str = $this->handler->read($this->sid);

        if (!empty($data_str)) {
            $session = json_decode($data_str, true);
            if (empty($session[self::TIME_KEY]) || empty($session[self::DATA_KEY]) || (intval($session[self::TIME_KEY]) + $this->lifetime < time())) {
                //会话过期处理
                $this->handler->destroy($this->sid);
                $this->session = array();
            } else {
                $this->session = $session[self::DATA_KEY];
            }
        }

        $this->writeflag = TRUE;   //回写打开
    }

    /**
     * 会话销毁
     */
    public function session_destory()
    {
        if (empty($this->sid)) {
            return;
        }

        if (!empty($this->session_cookie['cookie_enabled']) && $this->session_cookie['cookie_enabled'] == 1) {
            if (!empty($this->session_cookie['cookie_name'])) {
                $this->addCookie($this->session_cookie['cookie_name'], NULL, -1);
            }
            if (!empty($this->session_cookie['cookie_name_compatible'])) {
                $this->addCookie($this->session_cookie['cookie_name_compatible'], NULL, -1);
            }
        }

        $this->handler->destroy($this->sid);
    }

    /**
     * 关闭Session
     */
    public function session_write_close()
    {
        $this->session_save();
        $this->handler->close();
        $this->writeflag = FALSE;   //回写关闭
    }

    /**
     * 生成新的SID
     * @return string
     */
    public function generateSid()
    {
        $key = $_SERVER["SERVER_ADDR"];
        $key .= microtime();
        $key .= mt_rand(1000, 9999);
        $key .= mt_rand(1000, 9999);
        $key .= mt_rand(1000, 9999);
        $key .= mt_rand(1000, 9999);
        $key .= mt_rand(1000, 9999);
        if ($this->sid_function == 0) {
            return md5($key);
        }

        return sha1($key);
    }

    /**
     * 保存会话，TODO:目前没有加锁保护，如果并发情况多的话，可能要加锁
     */
    public function session_save()
    {
        if (!$this->writeflag) {  //回写关闭则直接返回
            return true;
        }
        if (empty($this->sid)) {
            throw new Exception('会话ID不存在异常!');
        }
        if (empty($this->session)) {
            return true;   //空则无需回写  //优化操作；
        }

        $session = array();
        $session[self::TIME_KEY] = time();
        $session[self::DATA_KEY] = $this->session;

        $data_str = json_encode($session);
        if (strlen($data_str) > $this->limitsize) {
            throw new Exception('会话超出限定大小：' . ($this->limitsize / 1024) . 'K');
        }

        if (!empty($data_str)) {
            $this->handler->write($this->sid, $data_str);
        }
    }

    /**
     * 设置cookie，防止重复写cookie
     */
    private function addCookie($name, $value, $expire = NULL)
    {
        $this->cookie_sets[$name] = array('value' => $value, 'expire' => $expire);
    }

    /**
     * 设置处理类
     * @param UnifyAuthHandler $handler
     */
    private function setHandler(UnifyAuthHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __destruct()
    {
        $this->session_write_close();

        //最后才回写cookie，防止同名cookie写多次
        foreach ($this->cookie_sets as $name => $set) {
            setcookie($name, $set['value'], $set['expire'], '/', $this->session_cookie['cookie_domain']);
        }
    }

}