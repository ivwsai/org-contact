<?php

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/2/12
 * Time: 下午4:19
 */
class Service_Login extends Service_Base
{
    public function login($params) {
        $account_model = new Model_Account($this->db_link);

        $res = null;
        if (Helper_Valid::email($_POST['username'])) {
            $res = $account_model->getInfoByEmail($_POST['username']);
        }

        if (empty($res) && Helper_Valid::mobilephone($_POST['username'])) {
            $res = $account_model->getInfoByMobile($_POST['username']);
        }

        if (empty($res)) {
            $res = $account_model->getInfoByUserName($_POST['username']);
        }

        if (empty($res)) {
            Helper_Http::writeJson(400, "帐号密码不正确");
        }

        if (!Helper_Security::validPassword($params['password'], $res['password'], 2)) {
            Helper_Http::writeJson(400, "帐号密码不正确");
        }

        $organization_model = new Model_Organization($this->db_link);
        $org_info = $organization_model->getInfo($res['org_id']);

        return array('user_id'=> $res['user_id'], "org_id"=>$res['org_id'], "org_name"=>$org_info['org_name'], "admin"=>1);
    }
}