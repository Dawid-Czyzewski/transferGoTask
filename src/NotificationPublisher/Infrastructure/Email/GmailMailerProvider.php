<?php

namespace App\NotificationPublisher\Infrastructure\Email;

use App\NotificationPublisher\Domain\Channel;
use App\NotificationPublisher\Domain\Message;
use App\NotificationPublisher\Domain\NotificationProviderInterface;
use App\NotificationPublisher\Domain\ProviderException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class GmailMailerProvider implements NotificationProviderInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function supports(string $channel): bool
    {
        return $channel === Channel::EMAIL;
    }

    public function getName(): string
    {
        return 'gmail';
    }

    public function send(Message $message): void
    {
        try {
            $email = (new Email())
                ->from($_ENV['GMAIL_FROM'] ?? 'no-reply@example.com')
                ->to($message->getRecipient())
                ->subject($message->getSubject())
                ->text($message->getContent());

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            throw new ProviderException('Gmail send failed: ' . $e->getMessage(), 0, $e);
        }
    }
}


