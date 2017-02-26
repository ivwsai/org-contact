<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * 主要用来实现一些控制器公用方法调用
 *
 */
abstract class Controller_Abs_Basic extends Netap_Controller
{
    public function before()
    {
        session_start();
        if (empty($_SESSION)
            && Netap_Request::$controller != 'Controller_Login'
            && Netap_Request::$controller != 'Controller_Register') {
            Helper_Http::redirect("/login");
        }

        /* 先执行上级控制器 */
        parent::before();

        /* 站点系统全局变量,配置相关 */
        $cfg = Netap_Config::config('system');
        define('SITE_VERSION', $cfg ['site_version']);
        define('BASE_URL', $cfg ['base_url']);
        define('THEME', $cfg ['theme']);
        define('JS_LIB', $cfg ['js_lib']);

        //去除魔术引号添加的转义反斜线
        $this->stripInput();

        //如果请求Content-Type为JSON，则正文自动解码并合到$_POST
        if(Helper_Http::isPostRequest() && Helper_Http::isJsonContent()){
            $this->receiveJSON2POST();
        }

        if(Helper_Http::isJsonContent() || Helper_Http::isAjaxHeader() || (isset ( $_GET ['dataType'] ) && $_GET ['dataType'] == 'json')){
            $this->returnJson = true;
        }
    }

    public function get_org_id() {
        return isset($_SESSION['org_id']) ? floatval($_SESSION['org_id']) : 0;
    }

    public function get_org_name() {
        return isset($_SESSION['org_name']) ? $_SESSION['org_name'] : "";
    }

    public function needAdmin() {
        if (!isset($_SESSION['admin'], $_SESSION['user_id']) || $_SESSION['admin'] != 1) {
            Helper_Http::writeJson(403, Netap_Lang::get('lang_system', 'not_have_permission'));
        }
    }

    /**
     * 防止继承该基类未实现index方法而报错
     */
    public function action_index()
    {
    }

    /**
     * 接收正文JSON内容到$_POST数组变量
     * @return bool
     */
    protected function receiveJSON2POST(){
        $inputdata = json_decode ( file_get_contents ( "php://input" ), true );

        $errMsg = NULL;
        switch (json_last_error()){
            case JSON_ERROR_NONE:
                is_array ($inputdata) && $_POST = $inputdata;
                break;
            case JSON_ERROR_DEPTH:
                $errMsg = "JSON解析超过允许的解析深度";
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errMsg = "JSON解析分隔符丢失错误";
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errMsg = "JSON解析控制符号错误，可能是因为编码问题";
                break;
            case JSON_ERROR_SYNTAX:
                $errMsg = "JSON解析语法错误";
                break;
            case JSON_ERROR_UTF8:
                $errMsg = "JSON解析非UTF8字符格式错误";
                break;
            default:
                $errMsg = "JSON解码发生未知错误";
        }

        if ($errMsg) {
            Netap_Logger::warn('receiveJSON2POST() Error：' . $errMsg);
            return FALSE;
        }

        return TRUE;
    }

    /**
     * 去除魔术引号添加的转义反斜线
     */
    protected function stripInput(){
        if (!get_magic_quotes_gpc ()){
            return ;
        }

        if (isset($_GET)) {
            $_GET = Helper_Http::stripSlashes( $_GET );
        }

        if (isset($_POST)) {
            $_POST = Helper_Http::stripSlashes( $_POST );
        }
    }
}
