<?php

/**
 * OAP 统一会话认证SDK
 *
 */
class OapAuth
{
    private $sid;
    private $unifyauth;

    public $session;  //只读会话数据

    function __construct($sid = NULL, $cfg_session = NULL)
    {
        //require_once 'unifyauth.php';
        $this->unifyauth = new UnifyAuth($cfg_session);
        $this->unifyauth->session_start($sid);
        $this->sid = $this->unifyauth->session_id();
        $this->session = $this->unifyauth->session;
    }

    /**
     * 判断当前会话是否本地 Session的 UAP登录
     * @return bool
     */
    public function isUapLogin()
    {
        return (isset ($this->session ['uap'] ['uid']) && intval($this->session ['uap'] ['uid']) > 0) ? true : false;
    }

    /**
     * 判断当前会话是否本地 Session的 OAP登录（必须存在身份才能是OAP登录）
     * @return bool
     */
    public function isOapLogin()
    {
        return (isset ($this->session ['oap'] ['oa_uid']) && intval($this->session ['oap'] ['oa_uid']) > 0) ? true : false;
    }

    /**
     * 判断当前身份是否所在单位的管理员
     * @throws Exception
     * @return bool
     */
    public function isUnitAdmin()
    {
        if (!$this->isOapLogin()) {
            return false;
        }

        $oauid = $this->getOaUid();
        if (isset ($this->session ['oaps'][$oauid]['isunitadmin']) && $this->session ['oaps'][$oauid]['isunitadmin'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * 判断当前身份是否所在单位的创建者
     * @throws Exception
     * @return bool
     */
    public function isUnitCreater()
    {
        if (!$this->isOapLogin()) {
            return false;
        }

        $oauid = $this->getOaUid();
        if (isset ($this->session ['oaps'][$oauid]['isunitcreater']) && $this->session ['oaps'][$oauid]['isunitcreater'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * 判断当前登录是否传入的机构的机构管理员
     * @throws Exception
     * @return bool
     */
    public function isOrgAdmin()
    {
        if (!$this->isOapLogin()) {
            return false;
        }

        $oauid = $this->getOaUid();
        if (isset ($this->session ['oaps'][$oauid]['isorgadmin']) && $this->session ['oaps'][$oauid]['isorgadmin'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * 判断当前用户是否拥有某个菜单权限
     * @param int $appid
     * @param string $menucode
     * @throws Exception
     * @return bool
     */
    public function hasPerm($appid, $permcode)
    {
        if (!$this->isOapLogin()) {
            return false;
        }

        //机构管理员和单位管理员拥有所有权限
        if ($this->isUnitAdmin() || $this->isOrgAdmin()) {
            return true;
        }

        if (isset ($this->session ['oap']['perms']) && is_array($this->session ['oap']['perms'])) {
            $findkey = $appid . '_' . $permcode;
            if (in_array($findkey, $this->session ['oap']['perms'])) {
                return true;
            }
        }

        return false;
    }


    /**
     * 获取当前用户编号
     * @throws Exception
     * @return int
     */
    public function getOaUid()
    {
        $this->needOapLogin();
        if (isset ($this->session['oap']['oa_uid'])) {
            return $this->session['oap']['oa_uid'];
        }
        throw new Exception('获取当前用户编号异常:未登录');
    }

    /**
     * 获取当前所在机构编号
     * @throws Exception
     * @return int
     */
    public function getOrgId()
    {
        $oauid = $this->getOaUid();
        if (isset ($this->session ['oaps'][$oauid]['orgid'])) {
            return $this->session ['oaps'][$oauid]['orgid'];
        }
        throw new Exception('获取当前所在机构编号异常:未登录');
    }

    /**
     * 获取当前所在单位编号
     * @throws Exception
     * @return int
     */
    public function getUnitId()
    {
        $oauid = $this->getOaUid();
        if (isset ($this->session ['oaps'][$oauid]['unit_id'])) {
            return $this->session ['oaps'][$oauid]['unit_id'];
        }
        throw new Exception('获取当前所在单位编号异常:未登录');
    }

    /**
     * 获取当前99帐号用户编号
     * @throws Exception
     * @return int
     */
    public function getUapUid()
    {
        $this->needUapLogin();
        if (isset ($this->session['uap']['uid'])) {
            return $this->session['uap']['uid'];
        }
        throw new Exception('获取当前99帐号用户编号异常:未登录');
    }

    /**
     * 获取当前99帐号登录帐号
     * @throws Exception
     * @return string
     */
    public function getUapAccount()
    {
        $this->needUapLogin();
        if (isset ($this->session['uap']['account'])) {
            return $this->session['uap']['account'];
        }
        throw new Exception('获取当前99帐号登录帐号异常:未登录');
    }

    /**
     * 检测必须99帐号登录，如果不是则抛出异常
     * @throws Exception
     */
    public function needUapLogin()
    {
        if (!$this->isUapLogin()) {
            throw new Exception('未登录99帐号异常');
        }
    }

    /**
     * 检测必须登录，且存在当前身份，如果不是则抛出异常
     * @throws Exception
     */
    public function needOapLogin()
    {
        if (!$this->isOapLogin()) {
            throw new Exception('未登录或不存在身份异常');
        }
    }

    /**
     * 检测当前身份是否是单位管理员，如果不是则抛出异常
     * @throws Exception
     */
    public function needUnitAdmin()
    {
        $this->needOapLogin();

        if (!$this->isUnitAdmin()) {
            throw new Exception('不是单位管理员异常');
        }
    }

    /**
     * 检测当前登录是机构管理员权限，如果不是则抛出异常
     * @throws Exception
     */
    public function needOrgAdmin()
    {
        $this->needOapLogin();

        if (!$this->isOrgAdmin()) {
            throw new Exception('不是机构管理员异常');
        }
    }

    /**
     * 在个人帐号登录状态下才能更改用户身份，且身份必须在当前身份列表
     * @param int $oauid
     * @return bool
     */
    public function changeUser($oauid)
    {
        if ($this->isUapLogin() && $this->isOapLogin() && is_array($this->unifyauth->session['oaps'])) {
            if ($this->unifyauth->session['oap']['oa_uid'] == $oauid) {
                return TRUE;
            }

            foreach ($this->unifyauth->session['oaps'] as $uid => $oapinfo) {
                if ($uid == $oauid) {
                    $this->unifyauth->session['oap']['oa_uid'] = $oauid;
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * 注销
     * @return bool
     */
    public function logout()
    {
        //$this->unifyauth->session=array();
        unset($this->unifyauth->session);
        return $this->unifyauth->session_destory();
    }

    /**
     * 会话保活
     */
    public function keepalive()
    {
        $this->unifyauth->session_start($this->sid);
        $this->unifyauth->session_save();
    }

    function __destruct()
    {
        $this->unifyauth->session_write_close();
    }

    /**
     * 获取uap会话id
     * @return string
     */
    public function getUapSid()
    {
        return $this->unifyauth->session_id();
    }
}