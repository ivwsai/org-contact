<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 部门新增，修改相关业务
 *
 */
class Service_Dept extends Service_Base
{
    /**
     * 新增部门
     * @param int $org_id 单位编号
     * @param array $depts 部门信息
     * @return bool
     */
    public function addDept($org_id, array $depts)
    {
        //新增部门信息到表org_depts,得到部门编号
        $model = new Model_Org_Dept($this->db_link);

        //部门名称是否存在
        $parent = isset($depts['parent_id']) ? intval($depts['parent_id']) : 0;
        if ($model->existsDeptSibling($org_id, $depts['name'], $parent)) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_unit', 'deptname_isexist'));
        }

        //@TODO 部门长是否存在
        if (isset($depts['chief_uid']) && $depts['chief_uid'] > 0) {
            $org_user_model = new Model_Org_User($this->db_link);
            $res = $org_user_model->getUserInfo($org_id, $depts['chief_uid']);
            if (!$res) {
                Helper_Http::writeJson(400, Netap_Lang::get('lang_unit', 'user_nonexist'));
            }
        }

        return $model->addDept($org_id, $depts);
    }

    /*
     * 编辑部门
     * @param int $org_id 单位编号
     * @param int $old_dept 修改前信息
     * @param int $new_dept 要修改的信息
     * @return bool
     */
    public function editDept($org_id, array $old_dept, array $new_dept)
    {
        //更新部门信息
        $model = new Model_Org_Dept($this->db_link);

        //名称款传则认为是旧名称
        if (empty($new_dept['name'])) {
            $new_dept['name'] = $old_dept['name'];
        }

        //父id 是否有修改
        $parent = isset($new_dept['parent_id']) ? intval($new_dept['parent_id']) : $old_dept['parent_id'];

        //部门名称是否存在
        if ($model->existsDeptSibling($org_id, $new_dept['name'], $parent, $old_dept['dept_id'])) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_unit', 'deptname_isexist'));
        }

        //@TODO 部门长有变更
        if (isset($new_dept['chief_uid']) && $old_dept['chief_uid'] != $new_dept['chief_uid']) {

        }

        return $model->editDept($org_id, $old_dept['dept_id'], $new_dept);
    }

    /**
     * 删除部门
     *
     * @param $org_id
     * @param $dept_id
     * @return bool
     */
    public function deleteDept($org_id, $dept_id)
    {
        $model = new Model_Org_Dept($this->db_link);
        $org_user_model = new Model_Org_User($this->db_link);

        //是否有子部门
        if ($model->existsDeptSub($org_id, $dept_id)) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_unit', 'dept_subexist'));
        }

        //部门下是否有人员
        if ($org_user_model->existsUser($org_id, $dept_id)) {
            Helper_Http::writeJson(400, Netap_Lang::get('lang_unit', 'deptuser_exist'));
        }

        //删除部门信息
        return $model->deleteDept($org_id, $dept_id);
    }

    /**
     * 获取部门信息
     *
     * @param $org_id
     * @param $deptid
     * @return array|null
     */
    public function getDeptInfo($org_id, $deptid)
    {
        $model = new Model_Org_Dept($this->db_link);
        $result = $model->getDeptInfo($org_id, $deptid);
        if ($result) {
            $result = Helper_Data::formatType($result, Model_Org_Dept::$deptColumns, FALSE);

            //查找部门负责人
            $result['manager_name'] = '';
            if ($result['chief_uid'] > 0) {
                $model = new Model_Org_User($this->db_link);
                $res = $model->getUserInfo($org_id, $result['chief_uid']);
                if ($res) {
                    $result['manager_name'] = $res['name'];
                } else {
                    $result['chief_uid'] = 0;
                }
            }
        }
        return $result;
    }

    /**
     * 获取组织树
     *
     * @param $org_id
     * @param int $pid
     * @return array
     */
    public function getOrgTree($org_id, $pid = 0)
    {
        $model = new Model_Org_Dept($this->db_link);

        $result = $model->getDepts($org_id, 3000);
        foreach ($result as &$val) {
            isset($val['dept_id']) && $val['dept_id'] = intval($val['dept_id']);
            isset($val['parent_id']) && $val['parent_id'] = intval($val['parent_id']);
            isset($val['gid']) && $val['gid'] = intval($val['gid']);
            isset($val['chief_uid']) && $val['chief_uid'] = floatval($val['chief_uid']);
        }
        $model->buildDetpTree($result, 0, 'excess');

        $result = isset($result[$pid]['sub']) ? $result[$pid]['sub'] : (isset($result[$pid]) ? $result[$pid] : array());

        return $result;
    }

    /**
     * 部门
     *
     * @param $org_id
     * @return array
     */
    public function getAllDept($org_id)
    {
        $model = new Model_Org_Dept($this->db_link);
        $result = $model->getDepts($org_id, 5000);
        if ($result) {
            $result = Helper_Data::formatType($result, Model_Org_Dept::$deptColumns);
        }
        return $result;
    }

    /**
     * 按修改时间增量取数据
     *
     * @param $org_id
     * @param $update_time
     * @param $limit
     * @return array
     */
    public function getDeltaList($org_id, $update_time, $limit)
    {
        $model = new Model_Org_Dept($this->db_link);
        $result = $model->getDeltaList($org_id, $update_time, $limit);
        if ($result) {
            $result = Helper_Data::formatType($result, Model_Org_Dept::$deptColumns);
        }
        return $result;
    }
}