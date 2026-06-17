<?php
namespace RyTM\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host       = SMTP_HOST;
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = SMTP_USER;
        $this->mail->Password   = SMTP_PASS;
        $this->mail->SMTPSecure = SMTP_SECURE;
        $this->mail->Port       = SMTP_PORT;
        $this->mail->setFrom(SMTP_USER, 'RYTM');
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendConfirmationEmail($to, $code)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->Subject = 'Подтверждение регистрации на RYTM';
            $this->mail->Body    = "<h2>Здравствуйте!</h2>
                                    <p>Ваш код подтверждения: <strong>{$code}</strong></p>
                                    <p>Срок действия кода – 15 минут.</p>
                                    <p>Если вы не регистрировались, проигнорируйте это письмо.</p>";
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mail error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }
}