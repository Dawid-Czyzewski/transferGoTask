<?php

namespace App\NotificationPublisher\Domain;

class Message
{
    public function __construct(
        private string $userId,
        private string $channel,
        private string $recipient,
        private string $subject,
        private string $content
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}


