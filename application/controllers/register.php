<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_register
{
    /**
     * 注册
     */
    public function action_index()
    {
        $validation = new Netap_Validation();
        $validation->addrule('username', Netap_Validation::NOT_EMPTY, "邮箱不能为空")
            ->addrule('username', Netap_Validation::EMAIL, "邮箱非法")
            ->addrule('password', Netap_Validation::NOT_EMPTY, "密码不能为空")
            ->addrule('password', Netap_Validation::RANGE_LENGTH, "密码长度必须是6-8位字符", array(6,8))
            ->addrule('re_password', Netap_Validation::NOT_EMPTY, "确认密码不一致", array(6,8))
            ->addrule('re_password', Netap_Validation::RANGE_LENGTH, "确认密码不一致", array(6,8))
            ->addrule('org_name', Netap_Validation::NOT_EMPTY, "公司名称不能为空")
            ->addrule('org_name', Netap_Validation::RANGE_LENGTH, "公司名称长度必须是2-50", array(2,50));

        if ($_POST['password'] != $_POST['re_password']) {
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => '确认密码不一致'));
        }

        if (!$validation->check($_POST)) {
            $errors = $validation->errors();
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
        }

        $service_register = new Service_Register();
        $res = $service_register->registerOrg($_POST);

        Helper_Http::writeJson(200, $res);
    }
}

