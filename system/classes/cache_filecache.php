<?php defined('SYSPATH') or die('No direct script access.');

if (!interface_exists('Netap_ICache')) {
    throw new Netap_Exception('不允许直接使用Netap_Cache_filecache，请从Netap_Cache类调用!');
}

/**
 *
 * Netap_Cache_filecache 处理类，不能直接使用，只能从Netap_Cache类调用
 * @package Netap
 * @category System
 *
 */
class Netap_Cache_filecache implements Netap_ICache
{
    /**
     * @var int 超时时间,单位秒
     */
    protected $lifetime = 0;

    /**
     * @var string KEY前缀
     */
    protected $prefix = '';

    /**
     * @var  file  缓存基准目录
     */
    protected $cachepath;

    /**
     * 构造函数
     * @param array $config 配置文件数组
     * @throws Netap_Exception
     */
    public function __construct($config)
    {
        if (!isset($config['filecache']['path'])) {
            throw new Netap_Exception('找不到缓存目录配置异常');
        }
        $pathprefix = $config['filecache']['path'];

        if (isset($config['lifetime'])) {
            $this->lifetime = intval($config['lifetime']);
        }

        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }

        try {
            $this->cachepath = new SplFileInfo($pathprefix);
        } catch (ErrorException $e) {
            $this->cachepath = $this->make_directory($pathprefix, 0777, TRUE);
        } catch (UnexpectedValueException $e) {
            $this->cachepath = $this->make_directory($pathprefix, 0777, TRUE);
        }

        if ($this->cachepath->isFile()) {
            throw new Netap_Exception('无法读取缓存[:resource]，缓存目录或文件已经存在!', 1, array(':resource' => $pathprefix));
        }

        if (!$this->cachepath->isReadable()) {
            throw new Netap_Exception('无法读取缓存[:resource]，请检查缓存目录是否可读!', 1, array(':resource' => $pathprefix));
        }

        if (!$this->cachepath->isWritable()) {
            throw new Netap_Exception('无法读取缓存[:resource]，请检查缓存目录是否可写!', 1, array(':resource' => $pathprefix));
        }
    }

    /**
     * 设置数据写入缓存
     * @param string $key 主键
     * @param Object $data 数据，通常是数组
     * @param int $lifetime 保存时间，按秒为单位
     * @param boolean $compress 是否压缩存储，内容较大时使用
     * @throws Netap_Exception
     * @throws ErrorException
     * @throws Exception
     * @return boolean
     */
    public function set($key, $data, $lifetime = NULL, $compress = FALSE)
    {
        $filename = self::filename($this->sanitize_id($key));
        $directory = $this->resolve_directory($filename);

        if ($lifetime === NULL) {
            $lifetime = $this->lifetime;
        }

        $dir = new SplFileInfo($directory);
        if (!$dir->isDir()) {
            if (!mkdir($directory, 0777, TRUE)) {
                throw new Netap_Exception(__METHOD__ . ' 无法创建目录 : :directory', 1, array(':directory' => $directory));
            }

            chmod($directory, 0777);
        }

        $resouce = new SplFileInfo($directory . $filename);
        $file = $resouce->openFile('w');

        $type = gettype($data);
        /* 序列化数据 */
        $data = json_encode((object)array(
            'payload' => ($type === 'string') ? $data : serialize($data),
            'expiry' => time() + $lifetime,
            'type' => $type
        ));
        $size = strlen($data);

        $file->fwrite($data, $size);
        $file->ftruncate($size);

        return (bool)$file->fflush();
    }

    /**
     * 获取缓存数据
     * @param string $key 主键
     * @param Object $default 如果读取失败，返回默认值
     * @throws Netap_Exception
     * @throws ErrorException
     * @return unknown|mixed
     */
    public function get($key, $default = NULL)
    {
        $filename = self::filename($this->sanitize_id($key));
        $directory = $this->resolve_directory($filename);

        $file = new SplFileInfo($directory . $filename);
        if (!$file->isFile()) {
            return $default;
        } else {
            $json = $file->openFile()->current();

            $data = json_decode($json);
            if (!isset($data->expiry)) {
                //throw new Netap_Exception(__METHOD__.' 读取文失内容失败 : :filename', 1, array(':filename' => $directory.$filename));
                Netap_Logger::error(__METHOD__ . ' 读取文失内容失败 : ' . $directory . $filename);
                $this->delete_file($file, NULL, TRUE);
                return $default;
            }

            if ($data->expiry < time()) {
                $this->delete_file($file, NULL, TRUE);
                return $default;
            } else {
                return ($data->type === 'string') ? $data->payload : unserialize($data->payload);
            }
        }
    }

    /**
     * 删除缓存数据
     * @param string $key 主键
     * @return boolean
     */
    public function delete($key)
    {
        $filename = self::filename($this->sanitize_id($key));
        $directory = $this->resolve_directory($filename);

        return $this->delete_file(new SplFileInfo($directory . $filename), NULL, TRUE);
    }

    /**
     * 清空缓存目录
     * @return boolean
     */
    public function delete_all()
    {
        return $this->delete_file($this->cachepath, TRUE);
    }

    /**
     * 删除文件
     * @param SplFileInfo $file
     * @param boolean $retain_parent_directory
     * @param boolean $ignore_errors
     * @param boolean $only_expired
     * @throws Netap_Exception
     * @throws Exception
     * @return boolean
     */
    protected function delete_file(SplFileInfo $file, $retain_parent_directory = FALSE, $ignore_errors = FALSE, $only_expired = FALSE)
    {

        try {
            if ($file->isFile()) {
                if ($only_expired === FALSE) {
                    $delete = TRUE;
                } else {
                    $json = $file->openFile('r')->current();
                    $data = json_decode($json);
                    $delete = isset($data->expiry) ? $data->expiry < time() : TRUE;
                }

                if ($delete === TRUE) {
                    unlink($file->getRealPath());
                }
            } elseif ($file->isDir()) {
                /* 创建目录迭代器 */
                $files = new DirectoryIterator($file->getPathname());

                while ($files->valid()) {
                    $name = $files->getFilename();

                    if ($name != '.' && $name != '..') {
                        /* 创建新的文件 */
                        $fp = new SplFileInfo($files->getRealPath());
                        /* 删除文件 */
                        $this->delete_file($fp);
                    }

                    /* 移动文件指针 */
                    $files->next();
                }

                if ($retain_parent_directory) {
                    return TRUE;
                }

                unset($files);
                return rmdir($file->getRealPath());
            }

        } catch (Exception $e) {
            if ($ignore_errors === TRUE) {
                Netap_Logger::error(__METHOD__ . ' 删除失败 :' . $e->getMessage());
                return FALSE;
            }

            throw $e;
        }
    }

    /**
     * 生成文件名
     * @param string $string
     * @return string
     */
    protected static function filename($string)
    {
        return sha1($string) . '.json';
    }

    /**
     * 根据文件获取文件路径
     * @param string $filename
     * @return string
     */
    protected function resolve_directory($filename)
    {
        return $this->cachepath->getRealPath() . DIRECTORY_SEPARATOR . $filename[0] . $filename[1] . $filename[2] . DIRECTORY_SEPARATOR . $filename[3] . $filename[4] . $filename[5] . DIRECTORY_SEPARATOR;
    }

    /**
     * 规格化主键
     * @param string $key
     * @return mixed
     */
    protected function sanitize_id($key)
    {
        $key = $this->prefix . $key;
        return str_replace(array('/', '\\', ' '), '_', $key);
    }

    /**
     * 创建缓存目录
     * @param string $directory
     * @param int|string $mode
     * @param bool $recursive
     * @param unknown_type $context
     * @return SplFileInfo
     * @throws Netap_Exception
     */
    protected function make_directory($directory, $mode = 0777, $recursive = FALSE, $context = NULL)
    {
        if (!mkdir($directory, $mode, $recursive, $context)) {
            throw new Netap_Exception('创建缓存目录失败 : :directory', 1, array(':directory' => $directory));
        }

        chmod($directory, $mode);

        return new SplFileInfo($directory);
    }
}
