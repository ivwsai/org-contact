<?php

require_once __DIR__ . '/vendor/autoload.php';

class Module_Resque
{

    /**
     * 添加后台任务
     * @param $queue_name
     * @param $workers
     * @param array $args
     * @param array $redis_config
     * <p>You can also use a DSN-style format: <br />
     * redis://user:pass@127.0.0.1:6379 <br />
     * redis://user:pass@a.host.name:3432/2 </p>
     * @throws Netap_Exception
     * @return string
     */
    public static function addTasks($queue_name, $workers, array $args, array $redis_config = null)
    {

        // You can also use a DSN-style format:
        //Resque::setBackend('redis://user:pass@127.0.0.1:6379');
        //Resque::setBackend('redis://user:pass@a.host.name:3432/2');

        if ($redis_config) {
            $database = 0;
            if (isset($redis_config['multiple_servers'])) {
                if (isset($redis_config['multiple_servers'][0]['database'])) {
                    $database = intval($redis_config['multiple_servers'][0]['database']);
                }

                Resque::setBackend($redis_config['multiple_servers'], $database);
            } else if ($redis_config['single_server']) {
                $address = "";
                $server = $redis_config['single_server'];
                if (isset($server['user'], $server['pass'])) {
                    $address .= "{$server['user']}:{$server['pass']}@";
                    unset($server['user'], $server['pass']);
                }
                if (isset($server['host'], $server['port'])) {
                    $address .= "{$server['host']}:{$server['port']}";
                    unset($server['host'], $server['port']);
                }

                if (!$address) {
                    throw new Netap_Exception("配置不正确");
                }

                $address = "redis://" . $address;
                if ($server) {
                    $address .= '?' . http_build_query($server);
                }

                if (isset($server['database'])) {
                    $database = intval($server['database']);
                }

                Resque::setBackend($address, $database);
            }
        } else {
            Resque::setBackend('127.0.0.1:6379');
        }

        $jobId = Resque::enqueue($queue_name, $workers, $args, true);

        return $jobId;
    }
}