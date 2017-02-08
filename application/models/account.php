<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/2/1
 * Time: 下午9:30
 */
class Model_Account extends Netap_Model
{
    public static $columns = array(
        'user_id' => 0,
        'mobile' => '',
        'username' => '',
        'email' => '',
        'password' => '',
        'status' => 0,
        'create_time' => '',
        'update_time' => 0,
    );

    /**
     * 创建帐号
     *
     * @param array $columns
     * @return bool|mixed
     */
    public function create(array $columns)
    {
        if ((!isset($columns['mobile']) || empty($columns['mobile']))
            && (!isset($columns['username']) || empty($columns['username']))
            && (!isset($columns['email']) || empty($columns['email']))) {
            return false;
        }

        $time = date('Y-m-d H:i:s', time());
        $columns['create_time'] = $time;
        $columns['update_time'] = $time;
        if (isset($columns['mobile']) && empty($columns['mobile'])) {
            $columns['mobile'] = null;
        }
        if (isset($columns['username']) && empty($columns['username'])) {
            $columns['username'] = null;
        }
        if (isset($columns['email']) && empty($columns['email'])) {
            $columns['email'] = null;
        }
        $password = isset($columns['password']) && !empty($columns['password']) ? $columns['password'] : '123456';
        $columns['password'] = Helper_Security::generatePassword($password);

        $columns = $this->prepareColumns(self::$columns, $columns);
        $sql = $this->bulidInsertSql('account', $columns);
        //echo $sql;exit;

        $result = $this->_db->query($sql, 'INSERT');
        if (!$result) {
            return false;
        }

        return $this->_db->insert_id();
    }

    /**
     * 根据用户id取得帐号信息
     *
     * @param int $user_id
     * @return array|null
     */
    public function getInfo($user_id) {
        $user_id = intval($user_id);

        $sql = "SELECT * FROM `account` WHERE `user_id`=$user_id";
        return $this->_db->fetch_first($sql);
    }

    /**
     * 根据用户id集取得帐号信息
     *
     * @param array $uids
     * @return array
     */
    public function getInfoByUids(array $uids) {
        array_walk($uids, function(&$item){ $item = (int)$item; });
        $uids = array_unique(array_filter($uids));
        if (empty($uids)) {
            return array();
        }

        $sql = "SELECT * FROM `account` WHERE `user_id` IN ($uids)";
        return $this->_db->fetch_all($sql);
    }

    /**
     * 根据手机号取得帐号信息
     *
     * @param string $mobile
     * @return null|array
     */
    public function getInfoByMobile($mobile) {
        $mobile = $this->_db->escapeString($mobile);
        $sql = "SELECT * FROM `account` WHERE `mobile`='$mobile' LIMIT 1";
        return $this->_db->fetch_first($sql);
    }

    /**
     * 根据用户名取得帐号信息
     *
     * @param string $username
     * @return null|array
     */
    public function getInfoByUserName($username) {
        $username = $this->_db->escapeString($username);
        $sql = "SELECT * FROM `account` WHERE `username`='$username' LIMIT 1";
        return $this->_db->fetch_first($sql);
    }

    /**
     * 根据用户名取得帐号信息
     *
     * @param string $email
     * @return null|array
     */
    public function getInfoByEmail($email) {
        $email = $this->_db->escapeString($email);
        $sql = "SELECT * FROM `account` WHERE `email`='$email' LIMIT 1";
        return $this->_db->fetch_first($sql);
    }

    /**
     * 设置密码
     *
     * @param $user_id
     * @param $password
     * @return array|object|query
     */
    public function setPassword($user_id, $password) {
        $time = date('Y-m-d H:i:s', time());
        $password = Helper_Security::generatePassword($password);

        $sql = "UPDATE `account` SET `update_time`='$time', `password`='$password' WHERE `user_id`=$user_id LIMIT 1";
        return $this->_db->query($sql, "UPDATE");
    }
}