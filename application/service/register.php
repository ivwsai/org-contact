<?php

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/2/12
 * Time: 下午3:34
 */
class Service_Register extends Service_Base
{
    function registerOrg($params) {
        $org_ds = Helper_Ds::getGlobalDS();
        $ds = new Netap_DsTa();
        $db_link = $ds->getDb($org_ds);

        $organization_model = new Model_Organization($db_link);
        if ($organization_model->getInfoByName($_POST['org_name'])) {
            Helper_Http::writeJson(400, "公司名称已经存在");
        }

        //username 填入的是邮箱
        $params['email'] = $params['username'];
        unset($params['username']);

        $account_model = new Model_Account($db_link);
        if ($account_model->getInfoByEmail($params['email'])) {
            Helper_Http::writeJson(400, "邮箱已注册");
        }

        $org_id = $organization_model->addOrg($params);
        if (!$org_id) {
            Helper_Http::writeJson(400, "注册公司失败");
        }

        $params['org_id'] = $org_id;
        $user_id = $account_model->create($params);
        if (!$user_id) {
            Helper_Http::writeJson(400, "注册公司后创建管理员失败");
        }

        if (!$ds->commit()) {
            return null;
        }

        return array("org_id"=>$org_id, "user_id"=>$user_id, "msg"=>"注册成功");
    }
}