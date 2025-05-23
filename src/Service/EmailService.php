<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{

    private $mailer;
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        string $from,
        string $to,
        string $subject,
        string $content
    )
    {
        // $from = $from ?? $_ENV['EMAIL_SENDER'];
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($content)
            ->html("<p>{$content}</p>");
        $this->mailer->send($email);
    }

}