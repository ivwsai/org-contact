<?php defined('SYSPATH') or die ('No direct script access.');
require_once MODPATH.'/SwiftMailer/swift_required.php';

class Controller_Register extends Controller_Abs_Basic
{
    /**
     * 注册
     */
    public function action_index()
    {
        $validation = new Netap_Validation();
        $validation->addrule('username', Netap_Validation::NOT_EMPTY, "邮箱不能为空")
            ->addrule('username', Netap_Validation::EMAIL, "邮箱非法")
            ->addrule('verify_code', Netap_Validation::NOT_EMPTY, "验证码不能为空")
            ->addrule('password', Netap_Validation::NOT_EMPTY, "密码不能为空")
            ->addrule('password', Netap_Validation::RANGE_LENGTH, "密码长度必须是6-8位字符", array(6,8))
            ->addrule('re_password', Netap_Validation::NOT_EMPTY, "确认密码不一致", array(6,8))
            ->addrule('re_password', Netap_Validation::RANGE_LENGTH, "确认密码不一致", array(6,8))
            ->addrule('org_name', Netap_Validation::NOT_EMPTY, "公司名称不能为空")
            ->addrule('org_name', Netap_Validation::RANGE_LENGTH, "公司名称长度必须是2-50", array(2,50));

        if ($_POST['password'] != $_POST['re_password']) {
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => '确认密码不一致'));
        }

        if (!$validation->check($_POST)) {
            $errors = $validation->errors();
            Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
        }

        //判断验证码是否正确
        if (!isset($_SESSION[$_POST['username']]) || empty($_SESSION[$_POST['username']]) || $_SESSION[$_POST['username']] != $_POST['verify_code']) {
          Helper_Http::writeJson(400, array('code' => 400, 'msg' => '验证码不正确'));
        }

        $service_register = new Service_Register();
        $res = $service_register->registerOrg($_POST);

        //清除验证码
        unset($_SESSION[$_POST['username']]);
        Helper_Http::writeJson(200, $res);
    }

    // 发送验证码
    public function action_send_verify_code() {
      $validation = new Netap_Validation();
      $validation->addrule('email', Netap_Validation::NOT_EMPTY, "邮箱不能为空")
          ->addrule('email', Netap_Validation::EMAIL, "邮箱非法");
      if (!$validation->check($_POST)) {
          $errors = $validation->errors();
          Helper_Http::writeJson(400, array('code' => 400, 'msg' => current($errors), 'errMsg' => $errors));
      }

      $service_register = new Service_Register();
      if ($service_register->checkRegMail($_POST['email'])) {
          Helper_Http::writeJson(400, "邮箱已注册");
      }

      // 必须间隔30秒
      $limit_key = 'send_tim:'.$_POST['email'];
      if (isset($_SESSION[$limit_key]) && $_SESSION[$limit_key]  > time() - 30) {
        Helper_Http::writeJson(400, "发送间隔必须大于30秒，请不要频繁发送");
      }

      $code = mt_rand(100000,999999);
      try {
        $package = array();
        $package['mail_myname'] = '狗不理企业通讯录';
        $package['mail_username'] = 'webmaster@gobelieve.io';
        $package['mail_password'] = 'xx';
        $package['mail_smtp'] = 'smtp.exmail.qq.com';
        $package['mail_port'] = '465';

        $package['to_list'] = array($_POST['email'] => $_POST['email']);
        $package['title'] = '狗不理企业通讯录-注册验证码';
        $package['content'] = '欢迎你注册狗不理企业通讯录，注册验证码是:'.$code;
        $res = self::send_mail($package);

        //设置 Session
        $_SESSION[$_POST['email']] = $code;
        $_SESSION[$limit_key] = time();
        Helper_Http::writeJson(200, "验证码发送成功，请登录邮箱查收");
      } catch(Exception $e) {
        Helper_Http::writeJson(500, $e->getMessage());
      }
    }

    /**
     * @send mail
     * @package 邮件信息
     *
     * @package['mail_myname']
     * @package['mail_username']
     * @package['mail_password']
     * @package['mail_smtp']
     * @package['mail_port']
     * @package['to_list'] => array( $email => $name ,  $email2 => $name2);
     * @package['title']
     * @package['content']
     *
     * @return int|string
     */
    private function send_mail( $package ) {

        $transport = Swift_SmtpTransport::newInstance( $package['mail_smtp'], $package['mail_port'], "ssl");
        $transport->setUsername( $package['mail_username']);
        $transport->setPassword( $package['mail_password']);

        $mailer = Swift_Mailer::newInstance( $transport);

        $message = Swift_Message::newInstance();
        $message->setFrom(array( $package['mail_username'] => $package['mail_myname']));
        $message->setTo( $package['to_list']);
        $message->setSubject( $package['title']);
        $message->setBody( $package['content'], 'text/html', 'utf-8');
        //$message->attach( Swift_Attachment::fromPath('pic.jpg', 'image/jpeg')->setFilename('rename_pic.jpg'));
        try{
            return $mailer->send($message);
        }catch (Swift_ConnectionException $e){
            return 'There was a problem communicating with SMTP: ' . $e->getMessage();
        }
    }
}
