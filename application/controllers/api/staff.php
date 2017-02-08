<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/1/31
 * Time: 下午2:49
 */
class Controller_Staff extends Controller_Abs_Basic
{
    public function action_add()
    {
        $this->needAdmin();

        //验证数据合法性
        $validation = new Netap_Validation();
        $validation->addrule('name', Netap_Validation::NOT_EMPTY, Netap_Lang::get('lang_unit', 'username_isempty'))
            ->addrule('name', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'username_tolong'), array(50))
            ->addrule('title', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'title_tolong'), array(20))
            ->addrule('dept_id', Netap_Validation::NOT_EMPTY, Netap_Lang::get('lang_unit', 'user_dept_isempty'))
            ->addrule('seq', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'seq_format_error'))
            ->addrule('birthday', Netap_Validation::DATE, Netap_Lang::get('lang_unit', 'birthday_format_error'))
            ->addrule('joindate', Netap_Validation::DATE, Netap_Lang::get('lang_unit', 'joindate_format_error'))
            ->addrule('email', Netap_Validation::EMAIL, Netap_Lang::get('lang_unit', 'email_format_error'))
            ->addrule('gender', Netap_Validation::RANGE, Netap_Lang::get('lang_unit', 'verify_user_gender', array(':min' => 0, ':max' => 2)), array(-1, 3))
            ->addrule('password', Netap_Validation::RANGE_LENGTH, Netap_Lang::get('lang_unit', 'password_strlen_error'), array(6, 10));

        if (!$validation->check($_POST)) {
            $errors = $validation->errors();
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
        }

        $user_service = new Service_User();
        $result = $user_service->addUser($this->get_org_id(), $_POST);

        $res = $user_service->getUserInfo($this->get_org_id(), $result);

        Helper_Http::writeJson(200, $res);
    }

    /**
     * 获取职员信息
     */
    public function action_info()
    {
        $user_id = !empty($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        if ($user_id <= 0) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_system', 'param_error'));
        }

        $user_service = new Service_User();
        $result = $user_service->getUserInfo($this->get_org_id(), $user_id);
        if (empty($result)) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_unit', 'deptuser_exist'));
        }

        Helper_Http::writeJson(200, $result);
    }

    /**
     * 获取职员列表
     */
    public function action_list()
    {
        //-1 代表不按部门筛选
        $dept_id = !empty($_GET['dept_id']) ? intval($_GET['dept_id']) : -1;
        $filter = array(
            'search' => '', //搜索用户名,通行证帐号,工号
            'dept_id' => -1 //默认所有部门,代表不按部门筛选
        );

        //部门
        if (isset($_GET['dept_id'])) {
            $filter['dept_id'] = (int)$_GET['dept_id'];
        }

        //是否查找子部门用户
        if (isset($_GET['getsub'])) {
            $filter['getsub'] = (int)$_GET['getsub'];
        }

        //按名字职员名字过滤
        if (isset($_GET['search'])) {
            $keywords = trim($_GET['search']);
            if (!empty($keywords)) {
                $filter['keywords'] = $keywords;
            }
        }

        //每页显示记录数
        $limit = (isset($_GET['size']) && is_numeric($_GET['size'])) ? abs($_GET['size']) : 100;
        $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? abs($_GET['page']) : 1;
        if ($limit > 500) {
            $limit = 100;
        }

        $offset = ($page - 1) * $limit;
        $user_service = new Service_User();
        $result = $user_service->getStaff($this->get_org_id(), $offset, $limit, $filter);
        Helper_Http::writeJson(200, $result);
    }

    /**
     * 删除职员
     */
    public function action_delete()
    {
        $this->needAdmin();

        $input_ids = !empty($_POST['uids']) ? array_unique($_POST['uids']) : array();
        $uids = array();
        foreach ($input_ids as $v) {
            if (intval($v)) $uids[] = intval($v);
        }

        if (empty($uids)) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_system', 'param_error'));
        }
        $user_service = new Service_User();
        $delete_ids = $user_service->deleteUser($this->get_org_id(), $uids);

        Helper_Http::writeJson(200, array("code" => 200, "msg" => Netap_Lang::get('lang_system', 'success'), "uids" => $delete_ids));
    }

    /**
     * 重置职员密码
     */
    public function action_resetpwd()
    {
        $this->needAdmin();

        $initialpwd = isset($_POST['password']) ? $_POST['password'] : '';
        $uids = isset($_POST['uids']) ? $_POST['uids'] : null;

        if (empty($uids) || !is_array($uids)) {
            Helper_Http::writeJson(400, array("code"=>400, "msg"=>Netap_Lang::get('lang_system', 'param_error')));
        }

        array_walk($uids, function(&$items){ $items = (int)$items;});
        $uids = array_unique(array_filter($uids));
        if (empty($uids)) {
            Helper_Http::writeJson(400, array("code"=>400, "msg"=>Netap_Lang::get('lang_system', 'param_error')));
        }

        if (strlen($initialpwd) < 6 || strlen($initialpwd) > 12) {
            Helper_Http::writeJson(400, array("code"=>400, "msg"=>Netap_Lang::get('lang_unit', 'password_format_error', array(':min'=>6, ':max'=>12))));
        }

        $user_service = new Service_User();
        $success_ids = $user_service->batchResetPassword($this->get_org_id(), $uids, $initialpwd);

        Helper_Http::writeJson(200, array("code" => 200, "msg" => Netap_Lang::get('lang_system', 'success'), "uids" => $success_ids));
    }

    /**
     * 编辑职员信息
     */
    public function action_edit()
    {
        $this->needAdmin();

        $validation = new Netap_Validation();
        $validation->addrule('user_id', Netap_Validation::NOT_EMPTY, Netap_Lang::get('lang_unit', 'user_id_isempty'))
            ->addrule('user_id', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'user_id_format_error'))
            ->addrule('name', Netap_Validation::NOT_EMPTY, Netap_Lang::get('lang_unit', 'username_isempty'))
            ->addrule('name', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'username_tolong'), array(50))
            ->addrule('title', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'title_tolong'), array(20))
            ->addrule('seq', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'seq_format_error'))
            ->addrule('birthday', Netap_Validation::DATE, Netap_Lang::get('lang_unit', 'birthday_format_error'))
            ->addrule('joindate', Netap_Validation::DATE, Netap_Lang::get('lang_unit', 'joindate_format_error'))
            ->addrule('email', Netap_Validation::EMAIL, Netap_Lang::get('lang_unit', 'email_format_error'))
            ->addrule('status', Netap_Validation::RANGE, Netap_Lang::get('lang_unit', 'verify_user_status'), array(-1, 2))
            ->addrule('gender', Netap_Validation::RANGE, Netap_Lang::get('lang_unit', 'verify_user_gender', array(':min' => 0, ':max' => 2)), array(-1, 3));

        if (!$validation->check($_POST)) {
            $errors = $validation->errors();
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
        }

        $user_service = new Service_User();
        $user_info = $user_service->getUserInfo($this->get_org_id(), $_POST['user_id']);
        if (empty($user_info)) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_unit', 'deptuser_exist'));
        }

        if (!$user_service->editUser($this->get_org_id(), $user_info, $_POST)) {
            Helper_Http::writeJson(500, Netap_Lang::get('lang_system', 'failure'));
        }

        $data = array_merge($user_info, $_POST);
        $data = Helper_Data::formatType($data, Model_Org_User::$columns, FALSE);
        Helper_Http::writeJson(200, $data);
    }

    /**
     * 按修改时间获取增量数据
     */
    public function action_delta_list()
    {
        $update_time = (isset($_GET['update_time']) && is_numeric($_GET['update_time'])) ? abs($_GET['update_time']) : 1485964788799;
        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ? abs($_GET['page']) : 100;
        if ($limit > 500) {
            $limit = 100;
        }

        $user_service = new Service_User();
        $result = $user_service->getDeltaList($this->get_org_id(), $update_time, $limit);
        Helper_Http::writeJson(200, $result);
    }
}