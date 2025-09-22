<?php

namespace App\NotificationPublisher\Application\MessageHandler;

use App\NotificationPublisher\Application\Message\SendNotificationMessage;
use App\NotificationPublisher\Application\NotificationService;
use App\NotificationPublisher\Application\Throttling\NotificationThrottler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendNotificationMessageHandler
{
    public function __construct(private NotificationService $service, private NotificationThrottler $throttler)
    {
    }

    public function __invoke(SendNotificationMessage $message): void
    {
        if (!$this->throttler->allow($message->getUserId())) {
            return;
        }
        $this->service->send(
            $message->getUserId(),
            $message->getChannels(),
            $message->getRecipient(),
            $message->getSubject(),
            $message->getContent()
        );
    }
}


