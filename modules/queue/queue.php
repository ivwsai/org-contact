<?php

/**
 *
 * @author SongDeQiang <mail.song.de.qiang@gmail.com>
 */
class Module_Queue
{

    const DRIVER_CLASS_PREFIX = 'Queue_Driver_';

    private $_driver;

    public $options;

    public function __construct($driver = 'redis', $options = array())
    {
        try {
            $file = __DIR__ . '/drivers/' . $driver . '.php';

            $class = self::DRIVER_CLASS_PREFIX . ucfirst($driver);

            class_exists($class) || is_file($file) && include($file);

            $this->_driver = new $class();

            if (!($this->_driver instanceof Interface_Queue)) {
                throw new Exception('类 ' . $class . ' 必须实现 Interface_Queue 接口！');
            }

            $this->options = array_merge((array)Helper_Arr::path(Netap_Config::config('queue'), $driver), $options);

            $this->_driver->init($this->options);
        } catch (Exception $e) {
            throw new Exception(__METHOD__ . '：' . $e->getMessage());
        }
    }

    /**
     * @return Netap_Validation
     */
    public function pushMailValidator()
    {
        $validator = new Netap_Validation();
        $validator->addrule('receivers', Netap_Validation::NOT_EMPTY, '收件人列表不可为空');
        $validator->addrule('receivers', 'is_array', '收件人列表必须为集合');
        $validator->addrule('receivers', function ($receivers) {
            return $receivers === array_filter($receivers, function ($v) {
                return filter_var($v, FILTER_VALIDATE_EMAIL);
            });
        }, '收件人列表必须为有效的邮件地址集合');
        $validator->addrule('subject', Netap_Validation::NOT_EMPTY, '邮件主题不可为空');
        $validator->addrule('content', Netap_Validation::NOT_EMPTY, '邮件正文不可为空');

        return $validator;
    }

    /**
     * $queue = new Module_Queue();
     *
     * $receivers = array(
     * '13812345678'
     * );
     * $content = 'This is sms content';
     *
     * $queue->pushSMS($receivers, $content);
     *
     * @param array $receivers
     * @param string $subject
     * @param string $content
     */
    public function pushMail($receivers, $subject, $content)
    {
        $data = compact('receivers', 'subject', 'content');
        $validator = $this->pushMailValidator();
        if (!$validator->check($data)) {
            Netap_Logger::info(__METHOD__ . '：邮件队列格式不正确。RawData：' . json_encode($data) . ';errors：' . json_encode($validator->errors()));

            return false;
        }

        $key = Helper_Arr::path($this->options, 'queue_keys.mail');

        $value = array(
            'mail_recv' => $receivers,
            'subject' => $subject,
            'content' => $content
        );

        try {
            return (bool)$this->pushQueue($key, json_encode($value));
        } catch (Exception $e) {
            Netap_Logger::info(__METHOD__ . '：邮件队列插入失败。RawData：' . json_encode($data) . ';errors：' . $e->getMessage());

            return false;
        }
    }


    /**
     * @return Netap_Validation
     */
    public function pushSMSValidator()
    {
        $validator = new Netap_Validation();
        $validator->addrule('receivers', Netap_Validation::NOT_EMPTY, '收件人列表不可为空');
        $validator->addrule('receivers', 'is_array', '收件人列表必须为集合');
        $validator->addrule('receivers', function ($receivers) {
            return $receivers === array_filter($receivers, function ($v) {
                return filter_var($v, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^1[3|4|5|8]\d{9}$/")));
            });
        }, '收件人列表必须为有效的手机号码集合');
        $validator->addrule('content', Netap_Validation::NOT_EMPTY, '短信内容不可为空');

        return $validator;
    }

    /**
     *
     * @param array $receivers
     * @param string $content
     */
    public function pushSMS($receivers, $content)
    {
        $data = compact('receivers', 'content');
        $validator = $this->pushSMSValidator();
        if (!$validator->check($data)) {
            Netap_Logger::info(__METHOD__ . '：短信队列格式不正确。RawData：' . json_encode($data) . ';errors：' . json_encode($validator->errors()));

            return false;
        }

        $key = Helper_Arr::path($this->options, 'queue_keys.sms');

        $value = array(
            'sms_recv' => $receivers,
            'content' => $content
        );

        try {
            return (bool)$this->pushQueue($key, json_encode($value));
        } catch (Exception $e) {
            Netap_Logger::info(__METHOD__ . '：短信队列插入失败。RawData：' . json_encode($data) . ';errors：' . $e->getMessage());

            return false;
        }
    }

    public function __call($method, $args)
    {
        if (method_exists($this->_driver, $method)) {
            $ref = new ReflectionMethod($this->_driver, $method);

            return $ref->invokeArgs($this->_driver, $args);
        } else if (is_callable(array($this->_driver, $method))) {
            return $this->_driver->__call($method, $args);
        } else {
            Netap_Logger::error(get_called_class() . '::' . $method . '() has not defined');
        }
    }
}

/**
 */
interface Interface_Queue
{

    public function init($options);

    public function pushQueue($key, $value);
}
