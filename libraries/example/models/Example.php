<?php
namespace libraries\example\models;

defined('SYSPATH') or die('No direct script access.');

/**
 *
 *  样例模型
 * @package Netap
 * @category Example
 *
 */
class Example extends \Netap_Model
{
    /**
     *
     * @return array
     */
    public function get_unit_list()
    {
        $sql = "SELECT unit_id, name FROM global_units_info limit 30";
        return $this->_db->query($sql, 'SELECT');
    }

    public function getdatabase()
    {
        return $this->_db->getDatabase();
    }
}