<?php defined('SYSPATH') or die('No direct access');

/**
 *
 * RabbitMQ操作类
 * @package Netap
 * @category System
 * @author  OAM Team
 *
 */
class Netap_Rabbitmq
{
    /**
     * 发送队列消息
     * @param array|string $config
     * @param string $message
     * @return bool
     * @throws Netap_Exception
     */
    public static function sendmsg($config = '', $message)
    {

        if (!is_array($config) || !isset($config['host'], $config['queue'])) {
            throw new Netap_Exception('配置项rabbitmq错误，请检查配置文件!');
        }

        $queue = $config['queue'];

        /* 消息体只记录前350个字符到日志中去 */
        $info = serialize($message);
        if (strlen($info) > 350) {
            $info = substr($info, 0, 350) . '...';
        }

        Netap_Logger::info('发送消息队列：queue_name=' . $queue . ' message=' . $info);
        try {
            if (class_exists('AMQPConnect')) {

                $con = new AMQPConnect($config);
                $exchange = new AMQPExchange($con);

                $exchange->declare('amq.direct', 'direct', AMQP_DURABLE);
                $exchange->bind($queue, $queue);

                return $exchange->publish(json_encode($message), $queue);
            } else if (class_exists('AMQPConnection')) {

                $con = new AMQPConnection ($config);
                $con->connect();

                $channel = new AMQPChannel($con);
                $exchange = new AMQPExchange($channel);

                $exchange->setName('amq.direct');
                $exchange->setType(AMQP_EX_TYPE_DIRECT);
                $exchange->setFlags(AMQP_DURABLE);
                $exchange->declare();
                $exchange->bind('amq.direct', $config['queue']);

                return $exchange->publish(json_encode($message), $config['queue']);
            } else {
                Netap_Logger::error('消息队列类AMQPConnect不存在错误，请检查是否安装AMQP扩展!');
                return FALSE;
            }
        } catch (Exception $e) {
            Netap_Logger::error('消息队列类发送消息时发生错误：' . $e->getMessage());
            return FALSE;
        }
    }

}
