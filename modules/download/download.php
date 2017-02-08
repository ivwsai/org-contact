<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 支持断点续传下载
 *
 * @package Module_Download

 * @copyright (c) 2010-2013 Team ND Inc.
 */
class Module_Download
{
    /**
     * 下载时文件指针偏移量
     *
     * @access private
     * @var int
     */
    private $offset;

    /**
     * 每一请求的块结束点
     *
     * @access private
     * @var int
     */
    private $chunked_end;

    /**
     * 文件存储路径
     *
     * @access private
     * @var string
     */
    private $filepath;

    /**
     * 文件下载时显示的文件名
     *
     * @access private
     * @var string
     */
    private $filename;

    /**
     * 文件句柄
     *
     * @access private
     * @var resource
     */
    private $handle;

    /**
     * 文件大小
     *
     * @access private
     * @var int
     */
    private $filesize;

    /**
     * 以什么方式调用
     *
     * @access private
     * @var mixed
     */
    private $var;

    public function __construct($var)
    {
        if (is_string($var)) {
            if (!is_file($var)) {
                throw new Exception('Failed to read a file or file does not exist.');
                return false;
            }

            $this->handle = fopen($var, "rb");
            $this->filesize = filesize($var);
            $this->filepath = $var;
        } else {
            $this->var = $var;
        }
    }

    /**
     * 设置文件大小
     *
     * @access public
     * @param int $size
     * @return int
     */
    public function set_filesize($size)
    {
        $this->filesize = (int)$size;
        return $this->filesize;
    }

    /**
     * 设置下载偏移量
     *
     * @access private
     * @return void
     */
    private function set_offset()
    {
        if (!$this->filesize) {
            throw new Exception('File size is zero.');
            return false;
        }

        if (isset($_SERVER['HTTP_RANGE']) && preg_match("/^bytes=(\d+)-(\d*)$/i", $_SERVER['HTTP_RANGE'], $match)) {

            $this->chunked_end = (int)$match[2];
            if ($this->chunked_end > $this->filesize || $this->chunked_end == 0) {
                $this->chunked_end = $this->filesize - 1;
            }

            $this->offset = (int)$match[1];
            if ($this->offset > $this->filesize) {
                $this->offset = 0;
            }

        } else {
            $this->offset = 0;
            $this->chunked_end = $this->filesize - 1;
        }
    }

    /**
     * 设置http头
     *
     * @access private
     * @return void
     */
    private function set_http_header()
    {
        if (!$this->filename) {
            throw new Exception('File name is empty.');
            return false;
        }

        $mime = 'application/octet-stream';

        $ext_name = strtolower(substr($this->filename, -3));
        if ($ext_name == 'ipa') {
            $mime = "application/iphone";
        } elseif ($ext_name == 'apk') {
            $mime = "application/vnd.android.package-archive";
        } else {
            $finfo = @finfo_open(FILEINFO_MIME);
            if (!is_null($finfo)) {
                $mime = @finfo_file($finfo, $this->filepath);
                @finfo_close($finfo);
            }
        }

        if (!empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE|Trident/', $_SERVER['HTTP_USER_AGENT'])) {
            $filename = str_replace('+', '%20', urlencode($this->filename));
        } else {
            $filename = $this->filename;
        }

        header("Content-Transfer-Encoding: binary");
        header("Cache-control: must-revalidate");
        header("Pragma: public");
        header("Content-Type: {$mime}");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        //header("Content-Length: " . ($this->filesize - $this->offset));
        header("Content-Length: " . (($this->chunked_end + 1) - $this->offset));

        if ($this->offset > 0) {
            header("HTTP/1.1 206 Partial Content");
            header("Content-Range: bytes {$this->offset}-{$this->chunked_end}/{$this->filesize}");
        } else {
            header("Accept-Ranges: bytes");
        }
    }

    /**
     * 下载
     *
     * @access public
     * @param string $filename 下载后的文件名,不填则为存储在磁盘中的文件名
     * @param string $callback 回调,第三方输出
     * @return void
     */
    public function down($filename = "", $callback = null)
    {
        if ($filename != "") {
            $this->filename = $filename;
        }

        $this->set_offset();
        $this->set_http_header();
        $this->send($callback);
    }

    /**
     * 发送数据
     *
     * @access private
     * @return void
     */
    private function send($callback)
    {
        if ($this->handle) {
            //fseek($this->handle, $this->offset);
            //fpassthru($this->handle);

            //指针偏移量
            $offset = $this->offset;
            while ($offset <= $this->chunked_end) {
                $block = ($this->chunked_end + 1) - $offset;
                $block = ($block > 8192 ? 8192 : $block);

                //移动文件指针
                fseek($this->handle, $offset);
                $file_buff = fread($this->handle, $block);

                echo $file_buff;
                #ob_flush();
                flush();

                $offset += 8192;
            }
        } else {
            //调用第三方数据输出函数
            if (is_callable(array($this->var, $callback))) {
                $file_buff = call_user_func_array(array($this->var, $callback), array($offset, $block));
            } else {
                throw new Exception('object->down(filename, callback) expects parameter 2 to be callback function.');
                return false;
            }
        }
    }

    public function __destruct()
    {
        $this->handle && fclose($this->handle);
    }
}

/**
 * 文件方式
 * e.g.
 *
 * $obj = new Module_Download("E:\\Tools\\360cse_5.5.0.614.exe");
 * $obj->down("386.msi");
 */

/**
 * 非文件方式
 * e.g.
 *
 *
 * //必须实现一个可指定读取某部份内容的回调方法供回调用
 * class myFile {
 * private $size;
 * private $handle;
 * public function __construct($filepath)
 * {
 * $this->handle = fopen($filepath, "rb");
 * $this->size = filesize($filepath);
 * }
 *
 * function read($offset, $size) {
 * fseek($this->handle, $offset);
 * return fread($this->handle, $size);
 * }
 *
 * function size(){
 * return $this->size;
 * }
 *
 * public function __destruct(){
 * fclose($this->handle);
 * }
 * }
 *
 * $filepath = "E:\\Tools\\360cse_5.5.0.614.exe";
 * $obj2 = new myfile($filepath);
 * $obj = new Module_Download($obj2);
 * $obj->set_filesize($obj2->size());
 * $obj->down("360cse.exe", "read");
 */
