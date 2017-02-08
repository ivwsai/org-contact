<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/1/31
 * Time: 下午2:49
 */
class Controller_Org extends Controller_Abs_Basic
{
    /**
     * 组织树
     */
    public function action_tree() {
        $dept_service = new Service_Dept();
        $data = $dept_service->getOrgTree($this->get_org_id());
        Helper_Http::writeJson(200, $data);
    }

    /**
     * 取获初始默认密码
     */
    public function action_getInitialpwd()
    {
        Helper_Http::writeJson(200, array (
            "code" => 200,
            "password" => 'abcdef',
            "msg" => Netap_Lang::get('lang_system', 'success')
        ));
    }
}