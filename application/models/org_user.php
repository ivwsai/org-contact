<?php defined('SYSPATH') or die('No direct script access.');

class Model_Org_User extends Netap_Model
{
    public static $columns = array(
        'org_uid' => 0,
        'org_id' => 0,
        'dept_id' => 0,
        'name' => '',
        'spell1' => '',
        'spell2' => '',
        'gender' => 0,
        'mobile' => '',
        'email' => '',
        'title' => '',
        'birthday' => '',
        'idcardno' => '',
        'xcardno' => '',
        'joindate' => '',
        'status' => 0,
        'seq' => 0,
        'seat' => '',
        'create_time' => '',
        'update_time' => 0,
    );

    /**
     * 新增组织用户
     *
     * @param $org_id
     * @param $columns
     * @return bool|mixed
     */
    public function addUser($org_id, array $columns)
    {
        //生日处理
        if (isset($columns['birthday']) && empty($columns['birthday'])) {
            $columns['birthday'] = null;
        }

        //生成拼音字段
        $spell = new Netap_Spell();
        $res = $spell->get_name_phonetic($columns['name'], '');
        $columns['spell1'] = $res['spell1'];
        $columns['spell2'] = $res['spell2'];
        $columns['create_time'] = date('Y-m-d H:i:s', time());
        $columns['org_id'] = floatval($org_id);

        $columns = $this->prepareColumns(self::$columns, $columns);

        $msec = round(microtime(true), 3) * 1000; //精确到毫秒
        $columns['update_time'] = $msec;

        $sql = $this->bulidInsertSql('org_user', $columns);
        //echo $sql;exit;
        $result = $this->_db->query($sql, 'INSERT');
        if (!$result) {
            return false;
        }

        return $this->_db->insert_id();
    }

    /**
     * 编辑组织用户信息
     *
     * @param $org_id
     * @param $user_id
     * @param $columns
     * @return bool
     */
    public function editUser($org_id, $user_id, array $columns)
    {
        $org_id = floatval($org_id);
        $user_id = floatval($user_id);
        if (isset($columns['birthday']) && empty(trim($columns['birthday']))) {
            $columns['birthday'] = null;
        }
        if (isset($columns['joindate']) && empty(trim($columns['joindate']))) {
            $columns['joindate'] = null;
        }

        //生成拼装的Sql语句
        $columns = $this->prepareColumns(self::$columns, $columns);
        $msec = round(microtime(true), 3) * 1000; //精确到毫秒
        $columns['update_time'] = $msec;
        $sql = $this->bulidUpdateSql("org_user", $columns, array("org_id=" => $org_id, "user_id=" => $user_id));
        try {
            //echo $sql;exit;
            $this->_db->query($sql, 'UPDATE');
        } catch (Exception $e) {
            Netap_Logger::error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 删除用户
     *
     * @param $org_id
     * @param $user_id
     * @return bool
     */
    public function deleteUser($org_id, $user_id)
    {
        $org_id = floatval($org_id);
        $user_id = floatval($user_id);
        $msec = round(microtime(true), 3) * 1000; //精确到毫秒

        $sql = "UPDATE `org_user` SET `update_time`=$msec, `status`=-1 WHERE `user_id`=$user_id AND `org_id`=$org_id";
        $this->_db->query($sql, 'UPDATE');
        return $this->_db->affected_rows() > 0;
    }

    /**
     * 获取组织用户信息
     *
     * @param $org_id
     * @param $user_id
     */
    public function getUserInfo($org_id, $user_id)
    {
        $org_id = floatval($org_id);
        $user_id = floatval($user_id);

        $sql = "SELECT * FROM `org_user` WHERE `user_id`=$user_id AND `org_id`=$org_id AND `status`>=0 LIMIT 1";
        return $this->_db->fetch_first($sql);
    }

    /**
     * 部门下是否有用户
     *
     * @param $org_id
     * @param $dept_id
     * @return bool
     */
    public function existsUser($org_id, $dept_id)
    {
        $org_id = floatval($org_id);
        $dept_id = floatval($dept_id);

        $sql = "SELECT 1 FROM `org_user` WHERE `org_id`=$org_id AND `dept_id`=$dept_id AND `status`>=0 LIMIT 1";
        $result = $this->_db->fetch_first($sql);

        if ($result) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取职员列表
     *
     * @param $org_id
     * @param $offset
     * @param $limit
     * @param array|null $filter
     * @return array
     */
    public function getStaff($org_id, $offset, $limit, array $filter = null)
    {
        $org_id = floatval($org_id);
        $offset = floatval($offset);
        $limit = floatval($limit);

        $sql = "SELECT SQL_CALC_FOUND_ROWS u.*,d.name AS deptname";
        $sql .= " FROM org_user u LEFT JOIN org_dept d ON (u.org_id=d.org_id AND u.dept_id=d.dept_id)";
        $sql .= " WHERE u.org_id=$org_id AND u.`status`>=0";

        //部门
        if (isset($filter['dept_id']) && $filter['dept_id'] >= 0) {
            $dept_id = (int)$filter['dept_id'];

            //查未分配、当前部门(不含子部门)用户；不递归
            if ($dept_id == 0 || (isset($filter['getsub']) && $filter['getsub'] == 0)) {
                $sql .= " AND u.dept_id=$dept_id";
            } else {
                $sql .= " AND (u.dept_id IN (SELECT s.dept_id FROM org_dept p LEFT JOIN org_dept s ON p.dept_id=s.parent_id WHERE p.org_id=$org_id AND s.org_id=$org_id AND (p.dept_id =$dept_id OR p.parent_id=$dept_id)) OR u.dept_id=$dept_id)";
            }
        }

        //搜索姓名,手机号
        if (isset($filter['keywords']) && !empty($filter['keywords'])) {
            $keywords = $this->_db->escapeString($filter['keywords']);
            $keywords = strtr($keywords, array('%' => '\%', '_' => '\_'));

            $search_mobile_str = '';
            if (is_numeric($filter['keywords'])) {
                $search_mobile_str = "u.mobile='$keywords' OR";
            }
            $sql .= " AND ($search_mobile_str u.name LIKE '%$keywords%' OR u.spell1 LIKE '$keywords%' OR u.spell2 LIKE '$keywords%')";
        }

        $sql .= " ORDER BY u.seq,u.spell2 ASC";
        $sql .= " LIMIT $offset, $limit";
        //echo $sql;exit;

        $total = 0;
        $result = $this->_db->fetch_all($sql);
        if (!empty($result)) {
            $total_query = $this->_db->fetch_first("SELECT FOUND_ROWS();");
            $total = floatval($total_query['FOUND_ROWS()']);
        }

        return array("total" => $total, "data" => $result);
    }

    /**
     * 根据手机号取得信息
     *
     * @param string $mobile
     * @return null|array
     */
    public function getInfoByMobile($org_id, $mobile) {
        $org_id = floatval($org_id);
        $mobile = $this->_db->escapeString($mobile);
        $sql = "SELECT * FROM `org_user` WHERE  `org_id`=$org_id AND `mobile`='$mobile' AND `status`>=0 LIMIT 1";
        return $this->_db->fetch_first($sql);
    }

    /**
     * 按修改时间正序取得
     *
     * @param $update_time
     * @param $limit
     * @return array
     */
    public function getDeltaList($org_id, $update_time, $limit){
        $org_id = floatval($org_id);
        $update_time = floatval($update_time);
        $limit = intval($limit);

        $sql = "SELECT * FROM `org_user` WHERE `org_id`=$org_id AND `update_time`>$update_time ORDER BY `update_time` ASC LIMIT $limit";

        return $this->_db->fetch_all($sql);
    }
}