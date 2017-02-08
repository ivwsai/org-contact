<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/1/31
 * Time: 下午2:49
 */
class Controller_Dept extends Controller_Abs_Basic
{
    /**
     * 新增部门
     */
    public function action_add()
    {
        $this->needAdmin();

        //验证数据合法性
        $validation = new Netap_Validation();
        $validation->addrule('name', Netap_Validation::NOT_EMPTY, Netap_Lang::get('lang_unit', 'deptname_isempty'))
            ->addrule('name', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'deptname_tolong'), array(20))
            ->addrule('shortname', Netap_Validation::NOT_EMPTY, Netap_Lang::get('lang_unit', 'shortname_isempty'))
            ->addrule('shortname', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'shortname_tolong'), array(20))
            ->addrule('parent_id', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'parent_format_error'))
            ->addrule('chief_uid', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'manager_format_error'))
            ->addrule('seq', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'seq_format_error'));

        if (!$validation->check($_POST)) {
            $errors = $validation->errors();
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
        }

        $dept_service = new Service_Dept();
        $dept_id = $dept_service->addDept($this->get_org_id(), $_POST);
        $_POST['dept_id'] = $dept_id;

        //格式化
        $data = Helper_Data::formatType($_POST, Model_Org_Dept::$deptColumns, FALSE);
        Helper_Http::writeJson(200, $data);
    }

    /**
     * 部门修改
     */
    public function action_edit()
    {
        $this->needAdmin();

        //验证数据合法性
        $validation = new Netap_Validation();
        $validation->addrule('dept_id', Netap_Validation::NOT_EMPTY, Netap_Lang::get('lang_unit', 'deptid_isempty'))
            ->addrule('dept_id', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'deptid_format_error'))
            ->addrule('name', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'deptname_tolong'), array(20))
            ->addrule('shortname', Netap_Validation::MAX_LENGTH, Netap_Lang::get('lang_unit', 'shortname_tolong'), array(20))
            ->addrule('parent_id', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'parent_format_error'))
            ->addrule('chief_uid', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'manager_format_error'))
            ->addrule('seq', Netap_Validation::NUMERIC, Netap_Lang::get('lang_unit', 'seq_format_error'));

        if (!$validation->check($_POST)) {
            $errors = $validation->errors();
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
        }

        $dept_service = new Service_Dept();
        $result = $dept_service->getDeptInfo($this->get_org_id(), $_POST['dept_id']);
        if (empty($result)) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_system', 'not_found'));
        }

        if (!$dept_service->editDept($this->get_org_id(), $result, $_POST)) {
            Helper_Http::writeJson(500, Netap_Lang::get('lang_system', 'failure'));
        }

        $data = array_merge($result, $_POST);
        $data = Helper_Data::formatType($data, Model_Org_Dept::$deptColumns, FALSE);
        Helper_Http::writeJson(200, $data);
    }

    /**
     * 删除部门
     */
    public function action_delete()
    {
        $this->needAdmin();

        $dept_id = !empty($_POST['dept_id']) ? intval($_POST['dept_id']) : 0;
        if ($dept_id <= 0) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_system', 'param_error'));
        }

        $dept_service = new Service_Dept();
        $dept_service->deleteDept($this->get_org_id(), $dept_id);
        Helper_Http::writeJson(200, Netap_Lang::get('lang_system', 'success'));
    }

    /**
     * 获取部门信息
     */
    public function action_info()
    {
        $dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;
        if ($dept_id <= 0) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_system', 'param_error'));
        }

        $dept_service = new Service_Dept();
        $result = $dept_service->getDeptInfo($this->get_org_id(), $dept_id);
        if (empty($result)) {
            Helper_Http::writeJson(404, Netap_Lang::get('lang_system', 'not_found'));
        }

        Helper_Http::writeJson(200, $result);
    }


    /**
     *  获取所有部门
     */
    public function action_all()
    {
        $dept_service = new Service_Dept();
        $data = $dept_service->getAllDept($this->get_org_id());
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

        $dept_service = new Service_Dept();
        $result = $dept_service->getDeltaList($this->get_org_id(), $update_time, $limit);
        Helper_Http::writeJson(200, $result);
    }
}