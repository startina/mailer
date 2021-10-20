<?php

declare(strict_types=1);
namespace Startina;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    protected $config = [];
    public function __construct(array $config)
    {
        $this->config = $config + require 'config.php';
    }

    /**
     * @param $msg
     * @param int $code
     * @throws \Exception
     */
    protected function halt($msg, $code = 4000)
    {
        throw new \Exception($msg, $code);
    }
    /**
     * 发送邮件方法
     * @param $to string 接收者
     * @param $title string 标题
     * @param $content string 邮件内容
     * @param $files string|array  [{path, name}]    附件，$path为路径， $name 为改附件名称，默认为文件名
     * @return bool
     * @throws \Exception
     */
    public function send($tos, $title, $content, $files = [])
    {
        $mail = new PHPMailer();
        $mail->SMTPDebug = (bool)$this->config['debug'];    // 是否开启debug
        $mail->isSMTP();                                // 使用smtp鉴权方式发送邮件
        $mail->SMTPAuth = true;                         // smtp需要鉴权 这个必须是true
        $mail->Host = $this->config['host'];            // 邮件服务器
        $mail->SMTPSecure = $this->config['secure'];    // 设置使用【secure】加密方式登录鉴权 --> ssl即可
        $mail->Port = $this->config['port'];            // 端口号
        $mail->Hostname = $this->config['hostname'];    // 设置发件人的主机域 可有可无 默认为localhost
        $mail->CharSet = $this->config['charset'];      // 设置发送的邮件的编码 可选GB2312/UTF-8
        $mail->FromName = $this->config['from_name'];   // 发件人姓名
        $mail->Username = $this->config['username'];    // 账号
        $mail->Password = $this->config['password'];    // 授权码
        $mail->From = $this->config['username'];        // 发件人邮箱地址
        $mail->isHTML(true);                    // 邮件正文是否为html编码

        //设置收件人邮箱地址 添加多个收件人 则多次调用方法即可
        if(!is_array($tos)){
            $tos = [$tos];
        }
        foreach ($tos as $to){
            $mail->addAddress($to);
        }
        //添加该邮件的主题
        $mail->Subject = $title;
        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = $content;

        // 为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
        if($files){
            if(!is_array($files)){
                $files = ['path' => $files];
            }
            if(!isset($files[0])){
                $files = [$files];
            }
            foreach ($files as &$file){
                if(!isset($file['path']) || !file_exists($file['path'])){
                    $this->halt('附件文件不存在');
                }
                if(!isset($file['name']) || !$file['name']){
                    $file['name'] = basename($file['path']);
                }
                $mail->addAttachment($file['path'], $file['name']);
                unset($file);   // 解除索引
            }
            // 发送邮件
        }
        //简单的判断与提示信息
        if(!$mail->send()) {
            $this->halt($mail->ErrorInfo);
        }
        return  true;
    }
}
