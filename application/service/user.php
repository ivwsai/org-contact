<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 组织用户新增，修改相关业务
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/2/1
 * Time: 下午8:55
 */
class Service_User extends Service_Base
{
    /**
     * 新增组织用户
     *
     * @param $org_id
     * @param $user
     * @return array|null
     */
    public function addUser($org_id, array $user)
    {
        $org_ds = Helper_Ds::getGlobalDS();
        $ds = new Netap_DsTa();
        $db_link = $ds->getDb($org_ds);

        $user_id = 0;
        $isBinding = true;
        $tipMsg = "";
        $account_model = new Model_Account($db_link);
        do {
            //手机号已经使用过
            if (isset($user['mobile']) && !empty($user['mobile'])) {
                $res = $account_model->getInfoByMobile($user['mobile']);
                if ($res) {
                    $user_id = $res['user_id'];
                    $tipMsg = "在组织内手机号已经作为帐号存在";
                    break;
                }
            }

            //用户名已经使用过
            if (isset($user['username']) && !empty($user['username'])) {
                $res = $account_model->getInfoByUserName($user['username']);
                if ($res) {
                    $user_id = $res['user_id'];
                    $tipMsg = "在组织内用户名已经作为帐号存在";
                    break;
                }
            }

            //邮箱已经使用过
            if (isset($user['email']) && !empty($user['email'])) {
                $res = $account_model->getInfoByEmail($user['email']);
                if ($res) {
                    $user_id = $res['user_id'];
                    $tipMsg = "在组织内邮箱已经作为帐号存在";
                    break;
                }
            }

            //明文密码加密储存
            $user['password'] = isset($user['password']) && !empty($user['password']) ? $user['password'] : '123456';
            $user_id = $account_model->create($user);
            $tipMsg = "创建系统帐号失败";
            $isBinding = false;
        } while (0);

        if ($user_id <= 0) {
            $ds->rollback();
            Helper_Http::writeJson(500, $tipMsg);
        }

        $user_model = new Model_Org_User($db_link);

        //如果不是新建判断用户在一个组织内只能有一条记录
        if ($isBinding) {
            $res = $user_model->getUserInfo($org_id, $user_id);
            if ($res) {
                $ds->rollback();
                Helper_Http::writeJson(400, $tipMsg);
            }
        }

        $user['user_id'] = $user_id;
        $user_model->addUser($org_id, $user);
        if (!$ds->commit()) {
            return null;
        }

        return $user_id;
    }

    /**
     * 编辑组织用户信息
     *
     * @param $org_id
     * @param $old_user
     * @param $new_user
     * @return bool
     */
    public function editUser($org_id, array $old_user, array $new_user)
    {
        //修改用户基本信息不能改密码
        if (isset($new_user['password'])) {
            unset($new_user['password']);
        }

        $user_model = new Model_Org_User($this->db_link);
        return $user_model->editUser($org_id, $old_user['user_id'], $new_user);
    }

    /**
     * 删除用户
     *
     * @param $org_id
     * @param array $uids
     * @return array
     */
    public function deleteUser($org_id, array $uids)
    {
        $delete_ids = array();
        $user_model = new Model_Org_User($this->db_link);
        foreach ($uids as $id) {
            if ($user_model->deleteUser($org_id, $id)) {
                $delete_ids[] = $id;
            }
        }

        return $delete_ids;
    }

    /**
     * 重置密码
     *
     * @param int $org_id
     * @param int $user_id
     * @param string $password
     * @return array|object|query
     */
    public function resetPassword($org_id, $user_id, $password)
    {
        $account_model = new Model_Account($this->db_link);
        return $account_model->setPassword($user_id, $password);
    }

    /**
     * 批量重置密码
     *
     * @param $org_id
     * @param array $uids
     * @param $password
     * @return array
     */
    public function batchResetPassword($org_id, array $uids, $password)
    {
        $success_ids = array();
        $account_model = new Model_Account($this->db_link);
        foreach ($uids as $id) {
            if ($account_model->setPassword($id, $password)) {
                $success_ids[] = $id;
            }
        }

        return $success_ids;
    }

    /**
     * 获取组织用户信息
     *
     * @param $org_id
     * @param $user_id
     * @param bool|null $return_account
     * @return mixed
     */
    public function getUserInfo($org_id, $user_id, $return_account = true)
    {
        $user_model = new Model_Org_User($this->db_link);
        $result = $user_model->getUserInfo($org_id, $user_id);
        if ($result) {
            $result = Helper_Data::formatType($result, Model_Org_User::$columns, FALSE);
            if ($return_account) {
                $account_model = new Model_Account($this->db_link);
                $account = $account_model->getInfo($result['user_id']);
                if ($account) {
                    if (!empty($account['mobile'])) {
                        $result['account'] = $account['mobile'];
                    } else if (!empty($account['username'])) {
                        $result['account'] = $account['username'];
                    } else if (!empty($account['email'])) {
                        $result['account'] = $account['email'];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取职员列表
     *
     * @param $org_id
     * @param $offset
     * @param $limit
     * @param array|null $filter
     * @param bool|null $return_account
     * @return mixed
     */
    public function getStaff($org_id, $offset, $limit, array $filter = null, $return_account = true)
    {
        $user_model = new Model_Org_User($this->db_link);
        $result = $user_model->getStaff($org_id, $offset, $limit, $filter);
        if (!empty($result['data'])) {
            $result['data'] = Helper_Data::formatType($result['data'], Model_Org_User::$columns);
            if ($return_account) {
                $account_model = new Model_Account($this->db_link);
                foreach ($result['data'] as &$val) {
                    $account = $account_model->getInfo($val['user_id']);
                    if ($account) {
                        if (!empty($account['mobile'])) {
                            $val['account'] = $account['mobile'];
                        } else if (!empty($account['username'])) {
                            $val['account'] = $account['username'];
                        } else if (!empty($account['email'])) {
                            $val['account'] = $account['email'];
                        }
                    }
                }
            }
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
        $user_model = new Model_Org_User($this->db_link);
        $result = $user_model->getDeltaList($org_id, $update_time, $limit);
        if ($result) {
            $result = Helper_Data::formatType($result, Model_Org_User::$columns);
        }
        return $result;
    }
}