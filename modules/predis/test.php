<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('MODPATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);


require_once MODPATH . '/Autoloader.php';
Predis\Autoloader::register();

function redis_version($info)
{
    if (isset($info['Server']['redis_version'])) {
        return $info['Server']['redis_version'];
    } elseif (isset($info['redis_version'])) {
        return $info['redis_version'];
    } else {
        return 'unknown version';
    }
}

class Module_Predis extends Predis\Client
{
    public function __construct($parameters = null, $options = null)
    {
        parent::__construct($parameters, $options);
    }
}

$redis = new Module_Predis(array(
    'host' => '192.168.94.22',
    'port' => 6379,
    //'user'     => 'root',
    //'pass'     => '1234',
    'database' => 0,
    'alias' => 'master',
    'master' => false,
));

$queue = 'queue_dynamic_msgs';
$class = 'dynamic_msgs';
$args = array(
    'business' => 'SIGNATURE',
    'uid' => 1,
    'unit_id' => 1,
    'time' => time(),
    'jsonbody' => json_encode(array('content' => '111'))
);

$id = md5(uniqid('', true));
$redis->sadd('resque:queues', $queue);
$length = $redis->rpush('resque:queue:' . $queue, json_encode(array(
    'class' => $class,
    'args' => array($args),
    'id' => $id,
    'queue_time' => microtime(true),
)));

$statusPacket = array(
    'status' => 1,
    'updated' => time(),
    'started' => time(),
);
$redis->set('resque:job:' . $id . ':status', json_encode($statusPacket));
