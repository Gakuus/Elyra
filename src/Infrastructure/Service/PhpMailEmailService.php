<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class PhpMailEmailService implements EmailServiceInterface
{
    public function __construct(
        private string $fromEmail = 'lainsmes@gmail.com',
        private string $fromName = 'Elyra Hospital',
    ) {
    }

    public function send(string $to, string $subject, string $htmlBody): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $this->fromEmail;
            /** @var string $smtpPass */
            $smtpPass = $_ENV['SMTP_PASSWORD'] ?? '';
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = str_replace(["\r", "\n"], '', $subject);
            $mail->Body = $htmlBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
}
