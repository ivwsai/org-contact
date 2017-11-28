<?php defined('SYSPATH') or die ('No direct script access.');


class MySessionHandler implements SessionHandlerInterface
{
    private $savePath;
    private $cookieName;
    private $secretKey;


    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }


     public static function urlsafeB64Encode($input)
     {
          return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
      }

      public static function encode( $payload,  $key,  $alg)
      {
          $jwt = self::urlsafeB64Encode(json_encode(['typ' => 'JWT', 'alg' => $alg])) . '.' . self::urlsafeB64Encode(json_encode($payload));
          return $jwt . '.' . self::signature($jwt, $key, $alg);
      }

     public static function signature( $input,  $key,  $alg)
      {
          if ($alg == "HS256") {
              $alg = "SHA256";
          } else if ($alg == "HS512") {
              $alg = "SHA512";
          } else if ($alg == "HS384") {
              $alg = "SHA384";
          }
          return self::urlsafeB64Encode(hash_hmac($alg, $input, $key, true));
      }

      public static function decode( $jwt,  $key)
      {
          $tokens = explode('.', $jwt);
          if (count($tokens) != 3)
              return false;

          list($header64, $payload64, $sign) = $tokens;

          $header = json_decode(self::urlsafeB64Decode($header64), JSON_OBJECT_AS_ARRAY);
          if (empty($header['alg']))
              return false;

          if (self::signature($header64 . '.' . $payload64, $key, $header['alg']) !== $sign)
              return false;

          $payload = json_decode(self::urlsafeB64Decode($payload64), JSON_OBJECT_AS_ARRAY);

          $time = $_SERVER['REQUEST_TIME'];
          if (isset($payload['iat']) && $payload['iat'] > $time)
              return false;

          if (isset($payload['exp']) && $payload['exp'] < $time)
              return false;

          return $payload;
      }


      public function serializeSessionData($array)
      {
          $result = '';
          foreach ($array as $key => $value) {
              $result .= $key . "|" . serialize($value);
          }
          return $result;
      }
      public function unSerializeSessionData($session_data)
      {
          $return_data = array();
          $offset = 0;
          while ($offset < strlen($session_data)) {
              if (!strstr(substr($session_data, $offset), "|")) {
                  throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
              }
              $pos = strpos($session_data, "|", $offset);
              $num = $pos - $offset;
              $varname = substr($session_data, $offset, $num);
              $offset += $num + 1;
              $data = unserialize(substr($session_data, $offset));
              $return_data[$varname] = $data;
              $offset += strlen(serialize($data));
          }
          return $return_data;
      }

      public function __construct($secretKey, $cookieName) {
          $this->secretKey = $secretKey;
          $this->cookieName = $cookieName;
      }

    public function open($savePath, $sessionName)
    {
        Netap_Logger::warn('open session:'.$savePath.' name:'.$sessionName);
        $this->savePath = $savePath;
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
       try {
            if (isset($_COOKIE[$this->cookieName])) {
                $data = $this->decode($_COOKIE[$this->cookieName], $this->secretKey);
                $val = $this->serializeSessionData($data);
                Netap_Logger::warn('session:'.var_export($data, true));
                return $val;
            }
            return '';
        } catch (Exception $ex) {
            Netap_Logger::warn("read session exception:".$ex);
            return '';
        }
    }

    public function write($id, $data)
    {
        $sessionObj = $this->unSerializeSessionData($data);
        $val = $this->encode($sessionObj, $this->secretKey, "HS256");

        if (!headers_sent()) {
            if ($data) {
                setcookie($this->cookieName, $val, 0, "/");
            } else {
                setcookie($this->cookieName, null, 0, "/");
            }
        }
        return true;
    }

    public function destroy($id)
    {
        if (!headers_sent()) {
            setcookie($this->cookieName, null, 0, "/");
        }
        return true;
    }

    public function gc($maxlifetime)
    {
        return true;
    }
}


/**
 * 主要用来实现一些控制器公用方法调用
 *
 */
abstract class Controller_Abs_Basic extends Netap_Controller
{
    public function before()
    {
        //todo放在配置文件中
        $secret = "eMaVFjhuWzcKxuQ8zC7w9SzBW2qiPP7u";
        $handler = new MySessionHandler($secret, "PHPSESSID");
        session_set_save_handler($handler, true);
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
