<?php defined('SYSPATH') or die ('No direct access');

/* 队列配置 */
return array(
    'redis' => array(
        'parameters' => array(
            'single_server' => array(
                'host' => '192.168.94.22',
                'port' => 6379,
                //'user'     => 'root',
                //'pass'     => '1234',
                'database' => 15,
                'alias' => 'master',
                'master' => false,
            ),
            /*
            'multiple_servers' => array(
                array(
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                    'database' => 15,
                    'alias'    => 'master',
                    'master'   => true,
                ),
                array(
                    'host'     => '127.0.0.1',
                    'port'     => 6380,
                    'database' => 15,
                    'alias'    => 'slave',
                    'master'   => false,
                ),
            )
            */
        ),
        'queue_keys' => array(
            'mail' => 'KEY_OAP_NOTICE_MAIL',
            'sms' => 'KEY_OAP_NOTICE_SMS',
        )
    ),
);
