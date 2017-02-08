<?php defined('SYSPATH') or die('No direct access allowed.');
return array(
    'single_server' => array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 15
    ),
    'multiple_servers' => array(
        array(
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 15,
            'alias' => 'first',
        ),
        array(
            'host' => '127.0.0.1',
            'port' => 6380,
            'database' => 15,
            'alias' => 'second',
        ),
    ),
    'replication' => array(
        'tcp://127.0.0.1:6379?database=15&alias=master',
        'tcp://127.0.0.1:6380?database=15&alias=slave',
    )
);
