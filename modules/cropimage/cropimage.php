<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 生成缩略图
 *
 * @package Modules_CropImage

 * @copyright (c) 2010-2013 Team ND Inc.
 */
class Module_CropImage
{

    private $mime;

    private $image_src;

    private $im;

    private static $imagick;

    private $image_data;

    public function __construct($filename = null)
    {
        if (!extension_loaded('imagick') && !extension_loaded('gd')) {
            throw new Exception('Couldn\'t find find imagick or gd extension.');
        }

        if (extension_loaded('imagick')) {
            self::$imagick = TRUE;
        } else {
            self::$imagick = FALSE;
        }

        if ($filename) {
            $this->readImage($filename);
        }

    }

    /**
     * Reads image from filename
     * @param $filename
     * @return void
     */
    private function readImage($filename)
    {
        $this->image_data = file_get_contents($filename);
        $this->image_src = $filename;
        $this->readImageBlob($this->image_data);
    }

    /**
     * Reads image from a binary string
     * @param $image
     * @throws Exception
     * @return bool
     */
    public function readImageBlob($image)
    {
        $format = $this->getTypeByBlob($image);

        $this->image_data = $image;
        $this->mime = strtolower($format);

        if (!$format) {
            throw new Exception('only support [jpg, png, gif, bmp] type data.');
        }

        if (self::$imagick) {
            if (!$this->im) {
                $this->im = new Imagick();
            } else {
                $this->im->clear();
            }
            $this->im->readImageBlob($this->image_data);
        } else {
            $this->im = imagecreatefromstring($this->image_data);

            if ($this->im === FALSE) {
                throw new Exception('Imagecreatefromstring create images like failure.');
            }
        }
    }

    /**
     * 获取图片类型
     * @return string
     */
    public function getImageMime()
    {
        if (!$this->im) {
            return null;
        }

        if ($this->mime) {
            $mime = 'image/' . $this->mime;
        } else {
            $mime = null;
        }

        return $mime;
    }

    /**
     * 取得图像宽
     * @return int
     */
    public function getImageWidth()
    {
        if (!$this->im) {
            return null;
        }

        if (self::$imagick) {
            $width = $this->im->getImageWidth();
        } else {
            $width = imagesx($this->im);
        }

        return $width;
    }

    /**
     * 取得图像高
     * @return int
     */
    public function getImageHeight()
    {
        if (!$this->im) {
            return null;
        }

        if (self::$imagick) {
            $height = $this->im->getImageHeight();
        } else {
            $height = imagesy($this->im);
        }

        return $height;
    }

    /**
     * 取得图像大小
     * @return int
     */
    public function getImageSize()
    {
        if (!$this->im) {
            return null;
        }

        if (self::$imagick) {
            //$size = $this->im->getImageSize();
            $size = $this->im->getImageLength();
        } else {
            $size = strlen($this->image_data);
        }

        return $size;
    }

    /**
     * 调整图片大小
     * @param int $maxwidth
     * @param int $maxheight
     * @param string $dst
     * @throws Exception
     * @return bool|string
     */
    public function resize($maxwidth, $maxheight, $dst = null)
    {
        if (!$this->im) {
            throw new Exception('open resource failure.');
        }

        $data = $this->resizeFromBlob($this->image_data, $maxwidth, $maxheight);
        if ($dst === null) {
            return $data;
        }

        $fp = fopen($dst, 'wb');
        $result = fwrite($fp, $data);

        //将缓冲内容输出到文件
        if ($result) {
            fflush($fp);
            ftruncate($fp, strlen($data));
        }
        fclose($fp);
        unset($data);

        return $result;
    }

    /**
     * 调整图片大小
     * @param string $input 图片二进制数据
     * @param int $maxwidth
     * @param int $maxheight
     * @throws Exception
     * @return string
     */
    public function resizeFromBlob($input, $maxwidth, $maxheight)
    {
        $image_format = $this->getTypeByBlob($input);
        if (!$image_format) {
            throw new Exception('only support [jpg, png, gif, bmp] type data.');
        }
        $this->mime = $image_format;

        if (self::$imagick) {
            if (!$this->im) {
                $this->im = new Imagick();
            } else {
                $this->im->clear();
            }
            $this->im->readImageBlob($input);


            $width = $this->im->getImageWidth();
            $height = $this->im->getImageHeight();

            $image_format = $this->im->getImageFormat();
            $image_format = strtolower($image_format);

            //原图比指定的还小不生成,返回原图,GIF需取第一帧
            if ($width < $maxwidth && $height < $maxheight && $image_format != 'gif') {
                return $input;
            }

            //计算实际宽高
            list($newwidth, $newheight) = $this->converSize($maxwidth, $maxheight, $width, $height);

            if ($image_format == 'gif') {
                //保证帧按顺序
                $this->im = $this->im->coalesceImages();

                $this->im->setCompression(Imagick::COMPRESSION_JPEG);
                $this->im->setCompressionQuality(90);

                //转为JPGE会自动丢弃其它帧
                $this->im->setFormat('jpeg');
            } else {
                //旋转为正常角度
                $this->rotateImage();
            }

            //$this->im->cropThumbnailImage( $newwidth, $newheight );
            $this->im->resizeimage($newwidth, $newheight, imagick::FILTER_LANCZOS, 0.9, TRUE);

            $this->im->stripImage();

            $output = $this->im->getImagesBlob();
        } else {
            $this->im = imagecreatefromstring($input);
            if ($this->im === FALSE) {
                throw new Exception('Imagecreatefromstring create images like failure.');
            }

            $this->im = $this->gdResizeImage($maxwidth, $maxheight, $image_format);

            ob_start();
            switch ($image_format) {
                case 'jpg':
                    imagejpeg($this->im, NULL, 90);
                    break;
                case 'png':
                    imagepng($this->im);
                    break;
                case 'gif':
                    imagegif($this->im);
                    break;
                case 'bmp':
                    imagewbmp($this->im);
                    break;
                default:
                    imagejpeg($this->im, NULL, 90);
            }
            $output = ob_get_contents();
            ob_end_clean();
        }

        //重置为生成后的数据
        $this->image_data = $output;

        return $output;
    }

    /**
     * 调整图片大小
     */
    private function gdResizeImage($maxwidth, $maxheight, $image_format)
    {
        $width = imagesx($this->im);
        $height = imagesy($this->im);

        //原图比指定的还小不生成
        if ($width < $maxwidth && $height < $maxheight && $image_format != 'gif') {
            return $this->im;
        }

        //计算实际宽高
        list($newwidth, $newheight) = $this->converSize($maxwidth, $maxheight, $width, $height);

        if (function_exists("imagecopyresampled")) {
            $newim = imagecreatetruecolor($newwidth, $newheight);

            //启用Alpha合成
            imagealphablending($newim, true);

            //启用抗锯齿
            imageantialias($newim, true);

            //启用Alpha通道
            imagesavealpha($newim, true);

            //创建透明颜色（最后一个参数0不透明，127完全透明）
            $bgcolor = ImageColorAllocateAlpha($newim, 255, 255, 255, 127);

            //使图片底色透明
            imagefill($newim, 0, 0, $bgcolor);

            imagecopyresampled($newim, $this->im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        } else {
            $newim = imagecreate($newwidth, $newheight);
            imagecopyresized($newim, $this->im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        }

        return $newim;
    }

    /**
     * 旋转图片角度
     * @param float $angle
     * @return void
     */
    private function rotateImage($angle = null)
    {
        if (self::$imagick) {
            if ($angle === null) {
                switch ($this->im->getImageOrientation()) {
                    case 1: // 水平(一般)
                        //
                        break;
                    case 2: // 水平鏡像
                        $this->im->flopImage();
                        break;
                    case 3: // 翻轉180度
                        $this->im->rotateImage(new ImagickPixel(), 180);
                        break;
                    case 4: // 垂直鏡像
                        $this->im->flipImage();
                        break;
                    case 5: // 水平鏡像後，順時鐘翻轉270度
                        $this->im->flopImage();
                        $this->im->rotateImage(new ImagickPixel(), 270);
                        break;
                    case 6: // 順時鐘翻轉90度
                        $this->im->rotateImage(new ImagickPixel(), 90);
                        break;
                    case 7: // 水平鏡像後，順時鐘翻轉90度
                        $this->im->flopImage();
                        $this->im->rotateImage(new ImagickPixel(), 90);
                        break;
                    case 8: // 順時鐘翻轉270度
                        $this->im->rotateImage(new ImagickPixel(), 270);
                        break;
                    default: // 讀取 EXIF Orientation 錯誤
                        break;
                }
            } else {
                $this->im->rotateImage(new ImagickPixel(), $angle);
            }
        } else {
            if ($angle !== null) {

            } else {

            }
        }
    }

    /**
     * 计算实际的宽高
     * @param int $dst_w
     * @param int $dst_h
     * @param int $src_w
     * @param int $src_h
     * @return array
     */
    private function converSize($dst_w, $dst_h, $src_w, $src_h)
    {
        $max = (int)max($dst_w, $dst_h);
        if ($src_w > $src_h) {
            $newwidth = $max > $src_w ? $max : (int)$src_w;
            $newheight = (int)round($src_h * ($max / $src_w));
        } else {
            $newheight = $max > $src_h ? $max : (int)$src_h;
            $newwidth = (int)round($src_w * ($max / $src_h));
        }

        return array($newwidth, $newheight);
    }

    /**
     * 获取图片类型
     * @param string $str
     * @return string
     */
    private function getTypeByBlob($str)
    {
        if (empty($str)) {
            return null;
        }

        $file_types = array(
            255216 => 'jpg',
            13780 => 'png',
            7173 => 'gif',
            6677 => 'bmp',
        );

        $str_info = unpack("C2chars", substr($str, 0, 2));
        $type_code = intval($str_info['chars1'] . $str_info['chars2']);
        $file_type = isset($file_types[$type_code]) ? $file_types[$type_code] : null;

        return $file_type;
    }

    public function __destruct()
    {
        //Free up memory
        if ($this->im) {
            if (self::$imagick) {
                $this->im->clear();
                $this->im->destroy();
            } else {
                imagedestroy($this->im);
            }
        }
    }
}
