<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 * 上传帮助类
 *
 * @package Netap
 * @category Helpers

 *
 */
class Helper_Upload
{

    /**
     * 检查上传是否成功
     * @param string $type
     * @return array
     */
    public static function check()
    {

        if (empty($_FILES)) {
            Helper_Http::writeJson(406, Netap_Lang::get('lang_attach', 'UPLOAD_ERR_NO_FILE'));
        }

        //取得配置的最大上传值
        $post_max_size = ini_get("upload_max_filesize");
        $scaleIndex = strtoupper(substr($post_max_size, -1));
        $post_max_size = (int)$post_max_size;

        $scale = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);
        if (isset($scale[$scaleIndex])) {
            $post_max_size = $post_max_size * $scale[$scaleIndex];
        }

        $msg = null;
        //只取第一个
        $files = reset($_FILES);
        switch ($files['error']) {
            case UPLOAD_ERR_OK:
                if ($files['size'] > $post_max_size) {
                    $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_CUSTOM_SIZE'), 'post_max_size' => $post_max_size);
                } else {
                    $files['post_max_size'] = $post_max_size;
                }
                break;
            case UPLOAD_ERR_INI_SIZE:
                $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_INI_SIZE'), 'post_max_size' => $post_max_size);
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_FORM_SIZE'), 'post_max_size' => $post_max_size);
                break;
            case UPLOAD_ERR_PARTIAL:
                $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_PARTIAL'), 'post_max_size' => $post_max_size);
                break;
            case UPLOAD_ERR_NO_FILE:
                $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_NO_FILE'), 'post_max_size' => $post_max_size);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_NO_TMP_DIR'), 'post_max_size' => $post_max_size);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_CANT_WRITE'), 'post_max_size' => $post_max_size);
                break;
            default:
                $msg = array('code' => 409, 'msg' => Netap_Lang::get('lang_attach', 'UPLOAD_ERR_UNKNOWN'), 'post_max_size' => $post_max_size);
        }

        return $msg ? $msg : $files;
    }

    /**
     * 分析分段上传请求参数合法性
     * @return array
     */
    public static function parseShardRequestParam()
    {

        $offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
        $filesize = isset($_POST['filesize']) ? intval($_POST['filesize']) : 0;
        $ticket = isset($_POST['ticket']) ? trim($_POST['ticket']) : '';
        $filemd5 = isset($_POST['md5']) ? trim($_POST['md5']) : '';

        //验证上传
        $files = Helper_Upload::check();
        if (isset($files['code'])) {
            return $files;
        }

        //上传完成标志:0文件上传结束,1上传文件块
        if (isset($_POST['flag'])) {
            $flag = intval($_POST['flag']);

            if (empty($filemd5)) {
                return array('code' => 406, 'msg' => "有flag时md5值必须传", 'post_max_size' => $files['post_max_size']);
            }

            if ($filesize <= 0) {
                return array('code' => 406, 'msg' => "有flag时filesize值必须传", 'post_max_size' => $files['post_max_size']);
            }

            if ($offset != 0 && $ticket == '') {
                return array('code' => 406, 'msg' => "有flag时offset>0时ticket值必须传", 'post_max_size' => $files['post_max_size']);
            }
        } else {
            $flag = 0;

            //一次性上传，文件大小为上传大小
            $filesize = $files['size'];
            $filemd5 = md5_file($files['tmp_name']);
        }

        if ($ticket != '' && strlen($ticket) != 32) {
            return array('code' => 406, 'msg' => "ticket值非法", 'post_max_size' => $files['post_max_size']);
        }

        return array('flag' => $flag, 'offset' => $offset, 'filesize' => $filesize, 'ticket' => $ticket, 'filemd5' => $filemd5, 'files' => $files);
    }
}