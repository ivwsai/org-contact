<?php defined('SYSPATH') or die ('No direct access');

/**
 * Netap_Pagination 分页处理类
 *
 * @package Netap
 * @category System
 *
 */
class Netap_Pagination implements ArrayAccess
{

    /**
     * 当前页码
     *
     * @var int
     */
    protected $current_page;

    /**
     * 总条数
     *
     * @var int
     */
    protected $total_items;

    /**
     * 每页条数
     *
     * @var int
     */
    protected $items_per_page;

    /**
     * 页码规格选择
     *
     * @var array
     */
    protected $items_per_page_arr = array(
        20,
        60,
        100
    );

    /**
     * 总页数
     *
     * @var int
     */
    protected $total_pages;

    /**
     * 当页应该显示的条开始序号
     *
     * @var int
     */
    protected $current_first_item;

    /**
     * 当页应该显示的条结束序号
     *
     * @var int
     */
    protected $current_last_item;

    /**
     * 上一页页码
     *
     * @var int
     */
    protected $previous_page;

    /**
     * 下一页页码
     *
     * @var int
     */
    protected $next_page;

    /**
     * 在视图是否显示首页链接,FALSE则不现实，其余则显示
     *
     * @var int boolean
     */
    protected $first_page;

    /**
     * 在视图是否显示末页链接,FALSE则不现实，其余则显示
     *
     * @var int boolean
     */
    protected $last_page;

    /**
     * 当前页之前存在多少条记录
     *
     * @var int
     */
    protected $offset;

    /**
     * 分页链接地址
     *
     * @var string
     */
    protected $page_url;

    /**
     * 分页替换url变量，如page=
     *
     * @var string
     */
    protected $page_key;

    /**
     * 页码分隔符，用于地址替换
     *
     * @var string
     */
    const PAGE_SEPARATE = '{PAGE}';

    /**
     * 计算指定页面的开始的位置
     *
     * @param number $page =1，显示页码
     * @param number $pageSize =20，每页显示条数
     * @return number 首条位置，从0开始
     */
    public static function calcFirstPos($page = 1, $pageSize = 20)
    {
        return (intval($page - 1) * intval($pageSize));
    }

    /**
     * 构建分页对象
     *
     * @example <p>
     *          $pagenation['current_page'] 当前页码 <br />
     *          $pagenation['total_items'] 总条数 <br />
     *          $pagenation['items_per_page'] 每页条数 <br />
     *          $pagenation['items_per_page_arr'] 页码规格选择 <br />
     *          $pagenation['total_pages'] 总页数 <br />
     *          $pagenation['current_first_item'] 当页应该显示的条开始序号 <br />
     *          $pagenation['current_last_item'] 当页应该显示的条结束序号 <br />
     *          $pagenation['previous_page'] 上一页页码 <br />
     *          $pagenation['next_page'] 下一页页码 <br />
     *          $pagenation['first_page'] 在视图是否显示首页链接,FALSE则不现实，其余则显示 <br />
     *          $pagenation['last_page'] 在视图是否显示末页链接,FALSE则不现实，其余则显示 <br />
     *          $pagenation['offset'] 当前页之前存在多少条记录
     *          $pagenation['page_url'] 当前页面地址，显示时会替换掉{PAGE}变量
     *          </p>
     * @param int $total_items ，总条数
     * @param int $current_page =1，当前显示页码，默认1
     * @param int $items_per_page =20，每页显示条数，默认20条
     * @param string $page_key ='page'，URL地址上的分页标识
     * @param bool|string $page_url =FALSE，分页跳转地址，FALSE取当前页
     * @throws Netap_Exception
     */
    function __construct($total_items, $current_page = 1, $items_per_page = 20, $page_key = 'page', $page_url = FALSE)
    {
        if (!is_numeric($current_page) || !is_numeric($total_items) || !is_numeric($items_per_page) || empty ($page_key)) {
            throw new Netap_Exception ('分页对象参数错误 ，请检查！');
        }

        $this->current_page = $current_page;
        $this->total_items = $total_items;
        $this->items_per_page = $items_per_page;
        $this->page_key = $page_key;

        if (!empty ($page_url)) {
            $url = urlencode($page_url);
        } else {
            /* 获取当前URL */
            $url = urlencode(Helper_Url::currentUrl());
        }

        /* %3D 表示= */
        $startpos = strrpos($url, urlencode($this->page_key) . '%3D');

        if (!$startpos) {
            $sept = '&';
            /* %3F 表示? */
            if (!strpos($url, '%3F')) {
                $sept = '?';
            }
            $this->page_url = urldecode($url) . $sept . self::PAGE_SEPARATE;
        } else {
            /* %26 表示& */
            $endpos = strpos($url, '%26', $startpos);
            if (!$endpos) {
                $endpos = strlen($url);
            }

            $this->page_url = Helper_Url::urlSpecialchars(urldecode(substr_replace($url, urlencode(self::PAGE_SEPARATE), $startpos, $endpos - $startpos)));
        }

        $this->calc();
    }

    /**
     * 计算分页信息
     */
    private function calc()
    {
        $this->total_items = ( int )max(0, $this->total_items);
        $this->items_per_page = ( int )max(1, $this->items_per_page);

        /* 计算总页数 */
        $this->total_pages = ( int )ceil($this->total_items / $this->items_per_page);
        /* 当前页码 */
        $this->current_page = ( int )min(max(1, $this->current_page), max(1, $this->total_pages));
        /* 计算当前条目开始的序号 */
        $this->current_first_item = ( int )min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
        /* 计算当前条目结束的序号 */
        $this->current_last_item = ( int )min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
        /* 计算上页页码，不存在返回FALSE */
        $this->previous_page = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
        /* 计算下页页码，不存在返回FALSE */
        $this->next_page = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
        /* 在视图是否显示首页链接 */
        $this->first_page = ($this->current_page === 1) ? FALSE : 1;
        /* 在视图是否显示末页链接 */
        $this->last_page = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
        /* 当前页之前存在多少条记录 */
        $this->offset = ( int )(($this->current_page - 1) * $this->items_per_page);
    }

    /**
     * 检查当前页是否存在
     *
     * @param int $page
     *            页码
     * @return boolean
     */
    public function valid_page($page)
    {
        if (!is_numeric($page))
            return FALSE;

        return $page > 0 and $page <= $this->total_pages;
    }

    /**
     * 获取当前页对应的URL
     *
     * @param int $page 为NULL则返回当前页
     * @return mixed
     */
    public function page_url($page = NULL)
    {
        if (!is_numeric($page)) {
            $page = $this->current_page;
        }
        return str_replace(self::PAGE_SEPARATE, $this->page_key . '=' . $page, $this->page_url);
    }

    /**
     * 设置对象，此方法忽略
     *
     * @param string $key
     * @param object $value
     * @throws Netap_Exception
     */
    public function offsetSet($key, $value)
    {
        throw new Netap_Exception ("分页对象Netap_Pagination不支持此方法");
    }

    /**
     * 判断是否存在对象
     *
     * @param mixed $key
     * @return bool
     * @internal param stirng $offset
     */
    public function offsetExists($key)
    {
        return isset ($this->$key);
    }

    /**
     * 删除对象，此方法忽略
     *
     * @param string $key
     * @throws Netap_Exception
     */
    public function offsetUnset($key)
    {
        throw new Netap_Exception ("分页对象Netap_Pagination不支持此方法");
    }

    /**
     * 获取对象
     *
     * @param string $key
     * @return object
     */
    public function offsetGet($key)
    {
        return isset ($this->$key) ? $this->$key : NULL;
    }
}