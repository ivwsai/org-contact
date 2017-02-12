<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 单位部门相关
 *
 * @package unit
 * @category dept
 * @author DC.L <ivwsai@gmail.com>
 */
class Model_Org_Dept extends Netap_Model
{
    public static $deptColumns = array(
        'dept_id' => 0,
        'org_id' => 0,
        'parent_id' => 0,
        'name' => '',
        'spell1' => '',
        'spell2' => '',
        'seq' => 0,
        'chief_uid' => 0,
        'status' => 0,
        'create_time' => '',
        'update_time' => 0,
    );

    /**
     * 新增部门
     *
     * @param int $org_id
     * @param array $ext_columns
     * @return int|FALSE
     */
    public function addDept($org_id, array $ext_columns = null)
    {
        if (!isset($ext_columns['name']) || empty($ext_columns['name'])) {
            return false;
        }

        $ext_columns['org_id'] = floatval($org_id);
        $ext_columns['create_time'] = date('Y-m-d H:i:s', time());
        if (!isset($ext_columns['chief_uid'])) {
            $ext_columns['chief_uid'] = 0;
        }

        if (!isset($ext_columns['spell1'], $ext_columns['spell2'])) {
            //生成拼音字段
            $spell = new Netap_Spell();
            $res = $spell->get_name_phonetic($ext_columns['name'], '');
            $ext_columns['spell1'] = $res['spell1'];
            $ext_columns['spell2'] = $res['spell2'];
        }

        $columns = $this->prepareColumns(self::$deptColumns, $ext_columns);
        $msec = round(microtime(true), 3) * 1000; //精确到毫秒
        $columns['update_time'] = $msec;

        $sql = $this->bulidInsertSql('org_dept', $columns);
        //echo $sql;exit;

        $result = $this->_db->query($sql, 'INSERT');
        if (!$result) {
            return false;
        }

        return $this->_db->insert_id();
    }


    /**
     * 编辑部门
     *
     * @param int $org_id
     * @param int $dept_id
     * @param array $depts
     * @return bool
     */
    public function editDept($org_id, $dept_id, array $depts)
    {
        //格式化参数
        $org_id = floatval($org_id);
        $dept_id = floatval($dept_id);

        if (isset($depts['name'])) {
            //生成拼音字段
            $spell = new Netap_Spell();
            $res = $spell->get_name_phonetic($depts['name'], '');
            $depts['spell1'] = $res['spell1'];
            $depts['spell2'] = $res['spell2'];
        }
        if (!isset($ext_columns['chief_uid'])) {
            $ext_columns['chief_uid'] = 0;
        }

        //生成拼装的Sql语句
        $columns = $this->prepareColumns(self::$deptColumns, $depts);
        $msec = round(microtime(true), 3) * 1000; //精确到毫秒
        $columns['update_time'] = $msec;
        $sql = $this->bulidUpdateSql("org_dept", $columns, array("org_id=" => $org_id, "dept_id=" => $dept_id));
        try {
            $this->_db->query($sql, 'UPDATE');
        } catch (Exception $e) {
            Netap_Logger::error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 删除部门
     *
     * @param int $org_id
     * @param int $dept_id
     * @return bool
     */
    public function deleteDept($org_id, $dept_id)
    {
        $org_id = floatval($org_id);
        $dept_id = floatval($dept_id);
        $msec = round(microtime(true), 3) * 1000; //精确到毫秒

        try {
            $sql = "UPDATE `org_dept` SET `update_time`=$msec, `status`=-1 WHERE `dept_id`=$dept_id AND `org_id`=$org_id";
            $this->_db->query($sql, 'UPDATE');
        } catch (Exception $e) {
            Netap_Logger::error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 根据单位ID获取所有部门(最多取1500)
     *
     * @param int $org_id
     * @return array
     */
    public function getDepts($org_id, $limit = 1500)
    {

        $org_id = floatval($org_id);
        $limit = floatval($limit);

        $sql = "SELECT `dept_id`, `parent_id`, `name`, `spell1`, `spell2`,`seq` FROM `org_dept` WHERE `org_id`=$org_id AND `status`=1";
        $sql .= " ORDER BY `seq` ASC, `dept_id` ASC LIMIT $limit";
        //echo $sql;exit;

        return $this->_db->fetch_all($sql);
    }

    /**
     * 构建部门树
     * @author liangdc
     *
     * @param array $data 扁平化的部门数据 id,parent_id,name必需
     * @param array $pid 顶级部门ID
     * @param mixed $excess 是否保留冗余数据
     * @return bool Returns true on success or false on failure.
     */
    public function buildDetpTree(array &$data, $pid = 0, $excess = FALSE)
    {

        $tree = array(
            0 => array()
        );

        foreach ($data as $key => &$val) {
            $tree[$val['dept_id']] = &$val;
        }

        if (!isset($tree[$pid])) {
            $data = array();
            return FALSE;
        }

        foreach ($data as $key => &$val) {
            $tree[$val['parent_id']]['sub'][] = &$val;
        }

        if ($excess === FALSE) {
            $data = isset($tree[$pid]['sub']) ? $tree[$pid]['sub'] : $tree[$pid];
        } else {
            $data = $tree;
        }
        $tree = null;

        return TRUE;
    }

    /**
     * 取得部门信息
     *
     * @param int $org_id
     * @param int $dept_id
     * @return array|$dept_id
     */
    public function getDeptInfo($org_id, $dept_id)
    {
        $org_id = floatval($org_id);
        $dept_id = floatval($dept_id);

        $sql = "SELECT * FROM org_dept WHERE dept_id=$dept_id AND org_id=$org_id AND `status`=1 LIMIT 1";
        return $this->_db->fetch_first($sql);
    }

    /**
     * 根据部门ID获取部门信息
     *
     * @param $org_id
     * @param array $dept_ids
     * @return array
     * @internal param int $org_id单位编号
     * @internal param array $dept_ids部门ID序列化值
     */
    public function getDeptsByIds($org_id, array $dept_ids)
    {
        $org_id = floatval($org_id);
        array_walk($dept_ids, function (&$item) {
            $item = floatval($item);
        });
        $dept_ids = implode(',', $dept_ids);

        $sql = "SELECT * FROM org_dept WHERE dept_id IN($dept_ids) AND org_id=$org_id AND `status`=1";

        return $this->_db->fetch_all($sql);
    }

    /**
     * 检查部门名称是否存在
     *
     * @param int $org_id
     * @param string $deptname
     * @param int parent
     * @param int $strip_id
     * @return bool
     */
    public function existsDeptSibling($org_id, $deptname, $parent = 0, $strip_id = null)
    {
        $org_id = floatval($org_id);
        $parent = floatval($parent);
        $deptname = $this->_db->escapeString($deptname);
        if ($strip_id) {
            $strip_id = floatval($strip_id);
            $sql = "SELECT 1 FROM `org_dept` WHERE `org_id`=$org_id AND `parent_id`=$parent AND `name`='$deptname' AND `dept_id`<>$strip_id AND `status`=1 LIMIT 1";
        } else {
            $sql = "SELECT 1 FROM `org_dept` WHERE `org_id`=$org_id AND `parent_id`=$parent AND `name`='$deptname' AND `status`=1 LIMIT 1";
        }
        //echo $sql;exit;
        $result = $this->_db->fetch_first($sql);

        if ($result) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 是否存在子部门
     *
     * @param int $org_id
     * @param int parent
     * @return bool
     */
    public function existsDeptSub($org_id, $parent = 0)
    {
        $org_id = floatval($org_id);
        $parent = floatval($parent);

        $sql = "SELECT 1 FROM `org_dept` WHERE `org_id`=$org_id AND `parent_id`=$parent AND `status`=1 LIMIT 1";
        $result = $this->_db->fetch_first($sql);

        if ($result) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 按修改时间正序取得部门列
     *
     * @param $update_time
     * @param $limit
     * @return array
     */
    public function getDeltaList($org_id, $update_time, $limit)
    {
        $org_id = floatval($org_id);
        $update_time = floatval($update_time);
        $limit = intval($limit);

        $sql = "SELECT * FROM `org_dept` WHERE `org_id`=$org_id AND `update_time`>$update_time ORDER BY `update_time` ASC LIMIT $limit";

        return $this->_db->fetch_all($sql);
    }
}