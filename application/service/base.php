<?php

/**
 * Created by PhpStorm.
 * User: dcliang
 * Date: 2017/2/1
 * Time: 下午2:20
 */
abstract class Service_Base
{
    protected $db_link = null;

    public function __construct()
    {
        $org_ds = Helper_Ds::getGlobalDS();
        $this->db_link = new Netap_Mysqli($org_ds);
    }
}