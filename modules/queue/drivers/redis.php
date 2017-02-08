<?php
/**
 * @author SongDeQiang <mail.song.de.qiang@gmail.com>
 */

/**
 *
 */
class Queue_Driver_Redis implements Interface_Queue
{
    private $_queue;

    public function init($options = array())
    {
        $this->_queue = new Module_Predis(Helper_Arr::path($options, array('parameters', 'single_server')), Helper_Arr::path($options, 'options'));
    }

    public function pushQueue($key, $value)
    {
        return $this->rpush($key, $value);
    }

    public function __call($method, $args)
    {
        if (method_exists($this->_queue, $method)) {
            $ref = new ReflectionMethod($this->_queue, $method);

            return $ref->invokeArgs($this->_queue, $args);
        } else if (is_callable(array($this->_queue, $method))) {
            return $this->_queue->__call($method, $args);
        } else {
            Netap_Logger::info(get_called_class() . '::' . $method . '() has not defined');
        }
    }
}