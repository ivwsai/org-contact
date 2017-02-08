<?php
require_once 'unifyauthhandler.php';

/**
 * 统一会话保存,File实现
 *
 */
class UnifyAuthHandler_File implements UnifyAuthHandler
{
    private $lifetime = 3600;
    private $totalweight;
    private $path_list;
    private $sid_path;  //保存最后一次请求的sid对应的server

    public function __construct($config)
    {
        $this->lifetime = $config['lifetime'];
        $this->totalweight = 0;
        $this->path_list = $config['save_path'];
        $this->sid_path = array();

        foreach ($config['save_path'] as $path) {
            $this->totalweight += $path['weight'];
        }
    }

    /**
     * 读会话数据
     */
    public function read($sid)
    {
        $pathinfo = $this->getSidPath($sid);
        $data = NULL;
        try {
            $file = new SplFileInfo($pathinfo['path'] . $sid . '.ses');

            if (!$file->isFile()) {
                return NULL;
            } else {
                $data = $file->openFile()->current();
            }
        } catch (ErrorException $e) {
            if ($e->getCode() === E_NOTICE) {
                throw new Exception(__METHOD__ . ' 反序列化对象失败，原因是 : ' . $e->getMessage(), 1);
            }

            throw $e;
        }

        return $data;
    }

    /**
     * 写会话数据
     */
    public function write($sid, $data)
    {
        $pathinfo = $this->getSidPath($sid);

        if ($data == NULL) {
            throw new Exception('空会话异常');
        }

        $dir = new SplFileInfo($pathinfo['path']);
        if (!$dir->isDir()) {
            if (!mkdir($pathinfo['path'], 0777, TRUE)) {
                throw new Exception(__METHOD__ . ' 无法创建目录 : :directory', 1, array(':directory' => $pathinfo['path']));
            }

            chmod($pathinfo['path'], 0777);
        }

        $resouce = new SplFileInfo($pathinfo['path'] . $sid . '.ses');
        $file = $resouce->openFile('w');

        $size = strlen($data);

        try {
            $file->fwrite($data, $size);
            return (bool)$file->fflush();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 销毁当前会话
     */
    public function destroy($sid)
    {
        $pathinfo = $this->getSidPath($sid);
        $file = new SplFileInfo($pathinfo['path'] . $sid . '.ses');
        try {
            if ($file->isFile()) {
                unlink($file->getRealPath());
            }
        } catch (ErrorException $e) {
            throw new Exception(__METHOD__ . ' 删除文件失败 : :file', 1, array(':file' => $file->getRealPath()));
        }
    }

    /**
     * 垃圾回收，系自动回收，暂时不做处理,后期补上
     */
    public function gc($lifetime)
    {
        return TRUE;
    }

    /**
     * 关闭缓存
     */
    public function close()
    {
        return TRUE;
    }

    /**
     * 关闭
     */
    public function __destruct()
    {
        return TRUE;
    }

    /**
     * 计算Sid保存的会话服务路径
     * @param string $sid
     * @throws Exception
     * @return array
     */
    private function getSidPath($sid)
    {
        if (!empty($this->sid_path['sid']) && $this->sid_path['sid'] == $sid && !empty($this->sid_path['path'])) {
            return $this->sid_path['path'];
        }

        if ($this->totalweight < 1) {
            throw new Exception('配置读取错误');
        }
        if ($this->totalweight == 1) {
            return $this->path_list[0];
        }

        //当前只支持CRC32
        $sid_checksum = sprintf("%u", crc32($sid));
        $sid_size = strlen($sid_checksum);
        $checksum = substr($sid_checksum, ($sid_size > 9) ? ($sid_size - 9) : 0);
        $sel_checksum = (intval($checksum) % $this->totalweight) + 1;

        $curweight = 0;
        $path = NULL;
        foreach ($this->path_list as $val) {
            $curweight += $val['weight'];
            if ($curweight >= $sel_checksum) {
                $path = &$val;
            }
        }

        if (empty($path)) {
            //如果取到的路径为空，则异常
            throw new Exception('Unknow save_path error!');
        }

        $this->sid_path['sid'] = $sid;
        $this->sid_path['path'] = &$path;
        return $path;
    }
}