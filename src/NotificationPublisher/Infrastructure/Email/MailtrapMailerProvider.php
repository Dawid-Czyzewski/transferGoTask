<?php

namespace App\NotificationPublisher\Infrastructure\Email;

use App\NotificationPublisher\Domain\Channel;
use App\NotificationPublisher\Domain\Message;
use App\NotificationPublisher\Domain\NotificationProviderInterface;
use App\NotificationPublisher\Domain\ProviderException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailtrapMailerProvider implements NotificationProviderInterface
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
        return 'mailtrap';
    }

    public function send(Message $message): void
    {
        try {
            $email = (new Email())
                ->from($_ENV['MAILTRAP_FROM'] ?? 'no-reply@example.com')
                ->to($message->getRecipient())
                ->subject($message->getSubject())
                ->text($message->getContent());

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            throw new ProviderException('Mailtrap send failed: ' . $e->getMessage(), 0, $e);
        }
    }
}


