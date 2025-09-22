<?php

namespace App\NotificationPublisher\Domain;

final class Channel
{
    public const EMAIL = 'email';
    public const SMS = 'sms';

    public static function isSupported(string $channel): bool
    {
        return in_array($channel, [self::EMAIL, self::SMS], true);
    }
}


