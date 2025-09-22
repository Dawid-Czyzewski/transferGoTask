<?php

namespace App\NotificationPublisher\Domain;

interface NotificationProviderInterface
{
    public function supports(string $channel): bool;

    /**
     * @throws ProviderException on failure
     */
    public function send(Message $message): void;

    public function getName(): string;
}


