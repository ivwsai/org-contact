<?php

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/2/12
 * Time: 下午3:45
 */
class Model_Organization extends Netap_Model
{
    public static $columns = array(
        'org_id' => 0,
        'org_name' => '',
        'create_time' => ''
    );

    /**
     * 新增组织
     *
     * @param $columns
     * @return bool|mixed
     */
    public function addOrg(array $columns)
    {

        $columns['create_time'] = date('Y-m-d H:i:s', time());

        $columns = $this->prepareColumns(self::$columns, $columns);

        $sql = $this->bulidInsertSql('organization', $columns);
        //echo $sql;exit;
        $result = $this->_db->query($sql, 'INSERT');
        if (!$result) {
            return false;
        }

        return $this->_db->insert_id();
    }

    /**
     * 编辑组织信息
     *
     * @param $org_id
     * @param $columns
     * @return bool
     */
    public function editOrg($org_id, array $columns)
    {
        $org_id = floatval($org_id);

        //生成拼装的Sql语句
        $columns = $this->prepareColumns(self::$columns, $columns);
        $sql = $this->bulidUpdateSql("organization", $columns, array("org_id=" => $org_id));
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
     * 获取组织信息
     *
     * @param $org_id
     * @return
     */
    public function getInfo($org_id)
    {
        $org_id = floatval($org_id);

        $sql = "SELECT * FROM `organization` WHERE `org_id`=$org_id LIMIT 1";
        return $this->_db->fetch_first($sql);
    }


    /**
     * 获取组织信息
     *
     * @param $org_name
     * @return
     */
    public function getInfoByName($org_name)
    {
        $org_name = $this->_db->escapeString($org_name);

        $sql = "SELECT * FROM `organization` WHERE `org_name`='$org_name' LIMIT 1";
        return $this->_db->fetch_first($sql);
    }
}