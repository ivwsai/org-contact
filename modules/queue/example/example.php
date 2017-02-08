<?php
/**
 * @author SongDeQiang <mail.song.de.qiang@gmail.com>
 * @license http://www.91.com/about
 */

/**
 * 队列使用
 */
class Controller_Example extends Controller_Abs_Basic
{
    /**
     *
     */
    public function action_index()
    {
        $queue = new Module_Queue();

        $receivers = array(
            'a@b.com'
        );
        $subject = 'This is email subject';
        $content = 'This is email content';

        $queue->pushMail($receivers, $subject, $content);

        $receivers = array(
            '13812345678'
        );
        $content = 'This is sms content';

        $queue->pushSMS($receivers, $content);
    }
}
