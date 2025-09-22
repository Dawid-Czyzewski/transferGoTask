<?php

namespace App\NotificationPublisher\Application\Config;

class NotificationConfig
{
    /** @var array<string,bool> */
    private array $channelsEnabled;

    /** @var array<string,string[]> */
    private array $providerOrderByChannel;

    public function __construct()
    {
        $this->channelsEnabled = [
            'email' => ($_ENV['CHANNEL_EMAIL_ENABLED'] ?? '1') === '1',
            'sms' => ($_ENV['CHANNEL_SMS_ENABLED'] ?? '1') === '1',
        ];

        $this->providerOrderByChannel = [
            'email' => self::parseList($_ENV['EMAIL_PROVIDERS'] ?? 'gmail,mailtrap'),
            'sms' => self::parseList($_ENV['SMS_PROVIDERS'] ?? 'twilio,textbelt'),
        ];
    }

    public function isChannelEnabled(string $channel): bool
    {
        return $this->channelsEnabled[$channel] ?? false;
    }

    /** @return array<int,string> */
    public function providersFor(string $channel): array
    {
        return $this->providerOrderByChannel[$channel] ?? [];
    }

    /**
     * @return array<int,string>
     */
    private static function parseList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));
    }
}


