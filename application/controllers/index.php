<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * 测试控制器入口
 *
 */
class Controller_Index extends Controller_Abs_Basic
{

    /**
     *
     * 默认入口
     * @internal param array $param
     */
    public function action_index()
    {
        $view = new Netap_View();
        $view->assign('unti_id', $this->get_org_id());
        $view->assign('unit_name', $this->get_org_name());
        $view->display('index');
    }
}
