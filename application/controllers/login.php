<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_Login extends Controller_Abs_Basic
{
    /**
     * 登录
     */
    public function action_index()
    {
        $view = new Netap_View();
        $view->assign("error", "");
        $view->display('login');
    }

    public function action_console(){
        $validation = new Netap_Validation();
        $validation->addrule('username', Netap_Validation::NOT_EMPTY, "邮箱不能为空")
            ->addrule('username', Netap_Validation::EMAIL, "邮箱非法")
            ->addrule('password', Netap_Validation::NOT_EMPTY, "密码不能为空")
            ->addrule('password', Netap_Validation::RANGE_LENGTH, "密码不正确", array(6,8));

        if (!$validation->check($_POST)) {
            $errors = $validation->errors();
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
        }

        $service_login = new Service_Login();
        $res = $service_login->login($_POST);
        if (!$res) {
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => '登录失败'));
        }

        $_SESSION = $res;
    }

    public function action_loginout() {
        $_SESSION = null;
        session_destroy();
        Helper_Http::redirect("/login");
    }

    //获取token
    public function action_token()
    {

    }

    //退出
    public function action_logout()
    {

    }
}

