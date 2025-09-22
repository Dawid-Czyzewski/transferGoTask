<?php

namespace App\NotificationPublisher\Application\Message;

class SendNotificationMessage
{
    /** @param array<int,string> $channels */
    public function __construct(
        private string $userId,
        private array $channels,
        private string $recipient,
        private string $subject,
        private string $content
    ) {
    }

    public function getUserId(): string { return $this->userId; }
    /** @return array<int,string> */
    public function getChannels(): array { return $this->channels; }
    public function getRecipient(): string { return $this->recipient; }
    public function getSubject(): string { return $this->subject; }
    public function getContent(): string { return $this->content; }
}


