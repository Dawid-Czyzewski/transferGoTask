<?php

namespace App\NotificationPublisher\Application\Usage;

use App\NotificationPublisher\Domain\Message;

class NotificationUsageTracker
{
    public function __construct(private string $logPath = __DIR__ . '/../../../../var/notifications.csv')
    {
        if (!is_dir(dirname($this->logPath))) {
            @mkdir(dirname($this->logPath), 0777, true);
        }
    }

    public function trackSent(Message $message, string $provider): void
    {
        $row = [
            date('c'),
            $message->getUserId(),
            $message->getChannel(),
            $message->getRecipient(),
            $provider,
            strlen($message->getContent()),
        ];
        $fp = fopen($this->logPath, 'ab');
        if ($fp) {
            fputcsv($fp, $row);
            fclose($fp);
        }
    }
}


