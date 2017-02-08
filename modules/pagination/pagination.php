<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Pagination links generator.
 *
 * @package    Kohana/Pagination
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 * @deprecated 为了兼容netap而改的，不赞成继续使用，新方法见path:system/classes/pagination.php
 */
class Module_Pagination implements ArrayAccess
{

    // Merged configuration settings
    protected $config = array(
        'current_page' => array(
            'source' => 'query_string',
            'key' => 'page'
        ),
        'total_items' => 0,
        'items_per_page' => 10,
        'view' => 'pagination/basic',
        'auto_hide' => FALSE,
        'first_page_in_url' => FALSE
    );

    // Current page number
    protected $current_page;

    // Total item count
    protected $total_items;

    // How many items to show per page
    protected $items_per_page;
    // How many items to show per page array
    protected $items_per_page_arr = array(15, 20, 40, 60);

    // Total page count
    protected $total_pages;

    // Item offset for the first item displayed on the current page
    protected $current_first_item;

    // Item offset for the last item displayed on the current page
    protected $current_last_item;

    // Previous page number; FALSE if the current page is the first one
    protected $previous_page;

    // Next page number; FALSE if the current page is the last one
    protected $next_page;

    // First page number; FALSE if the current page is the first one
    protected $first_page;

    // Last page number; FALSE if the current page is the last one
    protected $last_page;

    // Query offset
    protected $offset;

    /**
     * Creates a new Pagination object.
     *
     * @param   array  configuration
     * @return  Pagination
     */
    public static function factory(array $config = array())
    {
        return new Module_Pagination($config);
    }

    /**
     * Creates a new Pagination object.
     *
     * @param   array  configuration
     * @return  void
     */
    public function __construct(array $config = array())
    {
        // Overwrite system defaults with application defaults
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                if (isset($this->config[$key])) {
                    $this->config[$key] = $value;
                }
            }
        }
        // Pagination setup
        $this->setup($this->config);
    }

    /**
     * Loads configuration settings into the object and (re)calculates pagination if needed.
     * Allows you to update config settings after a Pagination object has been constructed.
     *
     * @param   array   configuration
     * @return  object  Pagination
     */
    public function setup(array $config = array())
    {
        // Only (re)calculate pagination when needed
        if ($this->current_page === NULL
            OR isset($config['current_page'])
            OR isset($config['total_items'])
            OR isset($config['items_per_page'])
        ) {

            // Retrieve the current page number
            if (!empty($this->config['current_page']['page'])) {
                // The current page number has been set manually
                $this->current_page = (int)$this->config['current_page']['page'];
            } else {
                switch ($this->config['current_page']['source']) {
                    case 'query_string' :
                        $this->current_page = isset($_GET[$this->config['current_page']['key']]) ? (int)$_GET[$this->config['current_page']['key']] : 1;
                        break;

                    case 'route' :
                        //@todo did not realize
                        $this->current_page = isset($_GET[$this->config['current_page']['key']]) ? (int)$_GET[$this->config['current_page']['key']] : 1;
                        break;
                }
            }

            // Calculate and clean all pagination variables
            $this->total_items = (int)max(0, $this->config['total_items']);
            $this->items_per_page = (int)max(1, $this->config['items_per_page']);
            $this->total_pages = (int)ceil($this->total_items / $this->items_per_page);
            $this->current_page = (int)min(max(1, $this->current_page), max(1, $this->total_pages));
            $this->current_first_item = (int)min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
            $this->current_last_item = (int)min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
            $this->previous_page = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
            $this->next_page = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
            $this->first_page = ($this->current_page === 1) ? FALSE : 1;
            $this->last_page = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
            $this->offset = (int)(($this->current_page - 1) * $this->items_per_page);
        }

        // Chainable method
        return $this;
    }

    /**
     * Generates the full URL for a certain page.
     *
     * @param   integer  page number
     * @return  string   page URL
     */
    public function url($page = 1)
    {

        // Clean the page number
        $page = max(1, (int)$page);
        // No page number in URLs to first page
        if ($page === 1 and !$this->config['first_page_in_url']) {
            $page = NULL;
        }
        switch ($this->config['current_page']['source']) {
            case 'query_string' :
                return Netap_URL::current_uri() . Netap_URL::query(array($this->config['current_page']['key'] => $page));

            case 'route' :
                return Netap_URL::site(Netap_URL::current_uri(array($this->config['current_page']['key'] => $page))) . Netap_URL::query();
        }

        return '#';
    }

    /**
     * Checks whether the given page number exists.
     *
     * @param   integer  page number
     * @return  boolean
     * @since   3.0.7
     */
    public function valid_page($page)
    {
        // Page number has to be a clean integer
        if (!is_numeric($page))
            return FALSE;

        return $page > 0 and $page <= $this->total_pages;
    }

    /**
     * Renders the pagination links.
     *
     * @param   mixed   string of the view to use, or a Kohana_View object
     * @return  string  pagination output (HTML)
     */
    public function render($view = NULL)
    {
        // Automatically hide pagination whenever it is superfluous
        //if ($this->config['auto_hide'] === TRUE AND $this->total_pages <= 1)
        //return '';
        if ($this->config['auto_hide']) {
            return '';
        }
        if ($this->total_items == 0) {
            return '';
        }

        if ($view === NULL) {
            // Use the view from config
            $view = $this->config['view'];
        }

        ob_start();
        $obj = new Netap_View();
        $obj->assign('pagination', $this);
        $obj->display($view);

        // Pass on the whole Pagination object
        return ob_get_clean();
    }

    /**
     * Renders the pagination links.
     *
     * @return  string  pagination output (HTML)
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Returns a Pagination property.
     *
     * @param   string  URI of the request
     * @return  mixed   Pagination property; NULL if not found
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : NULL;
    }

    /**
     * Updates a single config setting, and recalculates pagination if needed.
     *
     * @param   string  config key
     * @param   mixed   config value
     * @return  void
     */
    public function __set($key, $value)
    {
        $this->setup(array($key => $value));
    }

    /**
     * 获取当前页对应的URL
     * @param int $page 为NULL则返回当前页
     */
    public function page_url($page = NULL)
    {
        if (!is_numeric($page)) {
            $page = $this->current_page;
        }
        return $this->url($page);
    }

    /**
     * 设置对象，此方法忽略
     * @param string $key
     * @param object $value
     */
    public function offsetSet($key, $value)
    {
        throw new Netap_Exception("分页对象Netap_Pagination不支持此方法");
    }

    /**
     * 判断是否存在对象
     * @param stirng $offset
     */
    public function offsetExists($key)
    {
        return isset($this->$key);
    }

    /**
     * 删除对象，此方法忽略
     * @param string $key
     */
    public function offsetUnset($key)
    {
        throw new Netap_Exception("分页对象Netap_Pagination不支持此方法");
    }

    /**
     * 获取对象
     * @param string $key
     * @return object
     */
    public function offsetGet($key)
    {
        return isset($this->$key) ? $this->$key : NULL;
    }

} // End Pagination