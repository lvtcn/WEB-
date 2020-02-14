/*
     * 提交报价
     */
    public function bookBaojia()
    {
        $tbl = 'productbaojia';
        $xieyi = strpost('xieyi');
        $formdata = getformdata($tbl, 0);
        $formdata['createtime'] = time();
        Msg(empty($xieyi), 'error', 'Please agree and check the agreement');
        Msg(empty($formdata['prodchexing']), 'error', 'Please select a product type');
        Msg(empty($formdata['relxing']), 'error', 'Please enter a last name');
        Msg(empty($formdata['relname']), 'error', 'Please select a name');
        Msg(empty($formdata['prodname']), 'error', 'Please select a product name');
        Msg(empty($formdata['email']) || !chkemail($formdata['email']), 'error', 'Please enter your e-mail address');
        Msg(empty($formdata['prodcolor']), 'error', 'Please select product color');
        Msg(empty($formdata['phone']), 'error', 'Please enter your contact phone number');
        Msg(empty($formdata['gmtime']), 'error', 'Please select the product purchase time');
        Msg(empty($formdata['address']), 'error', 'Please enter your contact address');
        saveformdata($formdata, $tbl);
        $formdata['createtime'] = date("Y-m-d H:i:s");
        $this->sendtxt("{$formdata['relname']}提交报价通知", "book", $formdata, $_SERVER['DOCUMENT_ROOT']);
        Msg(true);
    }

    // 邮件数据替换
    public function sendtxt($title, $tpl, $data, $root)
    {
        $txt = file_get_contents("{$root}/inc/tpl/{$tpl}.txt");
        foreach ($data as $k => $val) {
            $txt = str_replace("{" . $k . "}", $val, $txt);
        }
        $this->smsEmail($title, $txt);
    }

    // 发送邮件
    public function smsEmail($Subject, $body)
    {
        require $_SERVER['DOCUMENT_ROOT'] . '/inc/PHPMailer/class.phpmailer.php';
        require $_SERVER['DOCUMENT_ROOT'] . '/inc/PHPMailer/class.smtp.php';

        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //服务器配置
            $mail->CharSet ="UTF-8";                     // 设定邮件编码
            $mail->SMTPDebug = 0;                        // 调试模式输出
            $mail->isSMTP();                             // 使用SMTP
            $mail->Host = 'smtp.163.com';                // SMTP服务器
            $mail->SMTPAuth = true;                      // 允许 SMTP 认证
            $mail->Username = 'greenman2020';            // SMTP 用户名  即邮箱的用户名
            $mail->Password = 'web203975417';            // SMTP 密码  部分邮箱是授权码(例如163邮箱)
            $mail->SMTPSecure = 'ssl';                   // 允许 TLS 或者ssl协议
            $mail->Port = 465;                           // 服务器端口 25 或者465 具体要看邮箱服务器支持

            $mail->setFrom('greenman2020@163.com', 'greenman2020@163.com');  //发件人

            $mail->addAddress('siyuanjunr@qq.com');  // 收件人
            $mail->addAddress('zhangdy@greenman.com.cn');  // 收件人
            $mail->addAddress('wangting@greenman.com.cn');  // 可添加多个收件人
            $mail->addAddress('Allen@greenman.com.cn');  // 可添加多个收件人

            $mail->addReplyTo('greenman2020@163.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
            //$mail->addCC('cc@example.com');                    //抄送
            //$mail->addBCC('bcc@example.com');                    //密送

            //发送附件
            // $mail->addAttachment('../xy.zip');         // 添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

            //Content
            $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject = $Subject;
            $mail->Body    = $body;
            $mail->AltBody = '如果邮件客户端不支持HTML则显示此内容';

            $mail->send();
            // echo '邮件发送成功';
        } catch (Exception $e) {
            // echo '邮件发送失败: ', $mail->ErrorInfo;
        }
    }
