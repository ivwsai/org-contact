<?php

/**
 * memcache保存类的实现接口
 *
 */
interface UnifyAuthHandler
{
    public function read($sid);                    //读会话

    public function write($sid, $data);        //写会话

    public function destroy($sid);                //销毁会话

    public function gc($lifetime);                //垃圾回收（适用于文件等场景，不适用于Key/Value数据库，因为无法清除）

    public function close();                //关闭缓存
}