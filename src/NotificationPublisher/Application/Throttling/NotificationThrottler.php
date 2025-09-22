<?php

namespace App\NotificationPublisher\Application\Throttling;

use Symfony\Component\RateLimiter\RateLimiterFactory;

class NotificationThrottler
{
    public function __construct(private RateLimiterFactory $notifications_userLimiter)
    {
    }

    public function allow(string $userId): bool
    {
        $limiter = $this->notifications_userLimiter->create($userId);
        $limit = $limiter->consume(1);
        return $limit->isAccepted();
    }
}


