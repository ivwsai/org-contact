<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * 视图处理类
 * @package Netap
 * @category System
 * @author  OAM Team
 *
 */
class Netap_View
{

    var $tpldir;
    var $objdir;

    var $tplfile;
    var $objfile;

    var $vars;
    var $force = 0;

    var $var_regexp = "\@?\\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*";
    var $vtag_regexp = "\<\?=(\@?\\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*)\?\>";
    var $const_regexp = "\{([\w]+)\}";

    var $languages = array();

    var $controller = "";
    var $action = "";

    function __construct()
    {
        $this->controller = strtolower(str_replace('Controller_', '', Netap_Request::$controller));
        $this->action = str_replace('action_', '', Netap_Request::$action);
        $this->template();
    }

    /**
     * 在控制器中授权变量给视图
     * @param string $k
     * @param object $v
     */
    public function assign($k, $v)
    {
        $this->vars[$k] = $v;
    }

    /**
     * 显示模版
     * @param string $file
     */
    public function display($file)
    {
        if ($this->vars) {
            extract($this->vars, EXTR_SKIP);
        }

        include $this->gettpl($file);
    }

    /**
     * 初始化模版类
     */
    private function template()
    {
        ob_start();
        $this->defaulttpldir = APPPATH . '/views';
        $this->tpldir = APPPATH . '/views';
        $this->objdir = APPPATH . '/tempfiles/views_obj/' . LANGUAGE;
        if (version_compare(PHP_VERSION, '5') == -1) {
            register_shutdown_function(array(&$this, '__destruct'));
        }
    }

    /**
     * 获取模版文件
     * @param string $file
     * @return string
     */
    private function gettpl($file)
    {
        $this->tplfile = $this->tpldir . '/' . $file . '.htm';
        $this->objfile = $this->objdir . '/' . $file . '.php';

        $tplfilemtime = @filemtime($this->tplfile);
        if ($tplfilemtime === FALSE) {
            $this->tplfile = $this->defaulttpldir . '/' . $file . '.htm';
        }

        if ($this->force || !file_exists($this->objfile) || @filemtime($this->objfile) < filemtime($this->tplfile)) {
            $this->complie();
        }
        return $this->objfile;
    }

    /**
     * 编译模版文件并生成目标缓存
     */
    private function complie()
    {
        $template = file_get_contents($this->tplfile);
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

        if (strncmp(phpversion(), '5.5', 3) >= 0) {
            $template = preg_replace_callback("/\{lang\s+([a-zA-Z0-9_\/\.]+?)\}/is", function ($matches) {
                return $this->lang($matches[1]);
            }, $template);

            $template = preg_replace("/\{($this->var_regexp)\}/", "<?=\\1?>", $template);
            $template = preg_replace("/\{($this->const_regexp)\}/", "<?=\\1?>", $template);
            $template = preg_replace("/(?<!\<\?\=|\\\\)$this->var_regexp/", "<?=\\0?>", $template);

            $template = preg_replace_callback("/\<\?=(\@?\\\$[a-zA-Z_]\w*)((\[[\\$\[\]\w]+\])+)\?\>/is", function ($matches) {
                return $this->arrayindex($matches[1], $matches[2]);
            }, $template);

            $template = preg_replace_callback("/\{\{eval (.*?)\}\}/is", function ($matches) {
                return $this->stripvtag('<?php ' . $matches[1] . '?>');
            }, $template);

            $template = preg_replace_callback("/\{eval (.*?)\}/is", function ($matches) {
                return $this->stripvtag('<?php ' . $matches[1] . '?>');
            }, $template);

            $template = preg_replace_callback("/\{for (.*?)\}/is", function ($matches) {
                return $this->stripvtag('<?php for(' . $matches[1] . ') {?>');
            }, $template);

            $template = preg_replace_callback("/\{elseif\s+(.+?)\}/is", function ($matches) {
                return $this->stripvtag('<?php } elseif(' . $matches[1] . ') {?>');
            }, $template);

            for ($i = 0; $i < 2; $i++) {
                $template = preg_replace_callback("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/is", function ($matches) {
                    return $this->loopsection($matches[1], $matches[2], $matches[3], $matches[4]);
                }, $template);

                $template = preg_replace_callback("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/is", function ($matches) {
                    return $this->loopsection($matches[1], '', $matches[2], $matches[3]);
                }, $template);
            }

            $template = preg_replace_callback("/\{if\s+(.+?)\}/is", function ($matches) {
                return $this->stripvtag('<?php if(' . $matches[1] . ') {?>');
            }, $template);

            $template = preg_replace("/\{template\s+(\w+?)\}/is", "<?php include \$this->gettpl('\\1');?>", $template);
            $template = preg_replace_callback("/\{template\s+(.+?)\}/is", function ($matches) {
                return $this->stripvtag('<?php include $this->gettpl(' . $matches[1] . '); ?>');
            }, $template);
        } else {
            $template = preg_replace("/\{lang\s+([a-zA-Z0-9_\/\.]+?)\}/ise", "\$this->lang('\\1')", $template);

            $template = preg_replace("/\{($this->var_regexp)\}/", "<?=\\1?>", $template);
            $template = preg_replace("/\{($this->const_regexp)\}/", "<?=\\1?>", $template);
            $template = preg_replace("/(?<!\<\?\=|\\\\)$this->var_regexp/", "<?=\\0?>", $template);

            $template = preg_replace("/\<\?=(\@?\\\$[a-zA-Z_]\w*)((\[[\\$\[\]\w]+\])+)\?\>/ies", "\$this->arrayindex('\\1', '\\2')", $template);

            $template = preg_replace("/\{\{eval (.*?)\}\}/ies", "\$this->stripvtag('<?php \\1?>')", $template);
            $template = preg_replace("/\{eval (.*?)\}/ies", "\$this->stripvtag('<?php \\1?>')", $template);
            $template = preg_replace("/\{for (.*?)\}/ies", "\$this->stripvtag('<?php for(\\1) {?>')", $template);

            $template = preg_replace("/\{elseif\s+(.+?)\}/ies", "\$this->stripvtag('<?php } elseif(\\1) { ?>')", $template);

            for ($i = 0; $i < 2; $i++) {
                $template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopsection('\\1', '\\2', '\\3', '\\4')", $template);
                $template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopsection('\\1', '', '\\2', '\\3')", $template);
            }
            $template = preg_replace("/\{if\s+(.+?)\}/ies", "\$this->stripvtag('<?php if(\\1) { ?>')", $template);

            $template = preg_replace("/\{template\s+(\w+?)\}/is", "<?php include \$this->gettpl('\\1');?>", $template);
            $template = preg_replace("/\{template\s+(.+?)\}/ise", "\$this->stripvtag('<?php include \$this->gettpl(\\1); ?>')", $template);
        }

        $template = preg_replace("/\{else\}/is", "<?php } else { ?>", $template);
        $template = preg_replace("/\{\/if\}/is", "<?php } ?>", $template);
        $template = preg_replace("/\{\/for\}/is", "<?php } ?>", $template);

        $template = preg_replace("/$this->const_regexp/", "<?=\\1?>", $template);

        $template = preg_replace("/\<\?=/is", "<?php echo ", $template);
        $template = preg_replace("/(\\\$[a-zA-Z_]\w+\[)([a-zA-Z_]\w+)\]/i", "\\1'\\2']", $template);

        umask(002);
        $dir = substr($this->objfile, 0, strrpos($this->objfile, '/'));
        /** 创建不存在目录 */
        if (!Helper_File::mkdirs($dir)) {
            throw new Netap_Exception('无法自动创建缓存目录：[' . $dir . ']请确保views_obj目录可写!');
        }

        $fp = fopen($this->objfile, 'w');
        fwrite($fp, $template);
        fclose($fp);
    }

    /**
     * 处理数组标记
     * @param string $name
     * @param object $items
     * @return string
     */
    private function arrayindex($name, $items)
    {
        $items = preg_replace("/\[([a-zA-Z_]\w*)\]/is", "['\\1']", $items);
        return "<?=$name$items?>";
    }

    /**
     * 处理eval标记
     * @param string $s
     * @return mixed
     */
    private function stripvtag($s)
    {
        return preg_replace("/$this->vtag_regexp/is", "\\1", str_replace("\\\"", '"', $s));
    }

    /**
     * 处理loop标记（循环）
     * @param array $arr
     * @param string $k
     * @param object $v
     * @param string $statement
     * @return string
     */
    private function loopsection($arr, $k, $v, $statement)
    {
        $arr = $this->stripvtag($arr);
        $k = $this->stripvtag($k);
        $v = $this->stripvtag($v);
        $statement = str_replace("\\\"", '"', $statement);
        return $k ? "<?php foreach((array)$arr as $k => $v) {?>$statement<?php }?>" : "<?php foreach((array)$arr as $v) {?>$statement<?php } ?>";
    }

    /**
     * 处理lang标记（语言包）
     * @param unknown_type $k
     * @return Ambigous <string, multitype:>
     */
    private function lang($k)
    {
        $lang = array();
        $pos = strrpos($k, ".");
        if ($pos > 0) {
            $file = substr($k, 0, $pos);
            $k = substr($k, $pos + 1);
            @include APPPATH . '/language/' . LANGUAGE . '/' . $file . '.php';
            $this->languages = array_merge($this->languages, $lang);
        }
        return !empty($this->languages[$k]) ? $this->languages[$k] : "{ $k }";
    }

    function __destruct()
    {
    }
}