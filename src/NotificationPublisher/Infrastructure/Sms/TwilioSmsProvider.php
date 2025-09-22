<?php

namespace App\NotificationPublisher\Infrastructure\Sms;

use App\NotificationPublisher\Domain\Channel;
use App\NotificationPublisher\Domain\Message;
use App\NotificationPublisher\Domain\NotificationProviderInterface;
use App\NotificationPublisher\Domain\ProviderException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TwilioSmsProvider implements NotificationProviderInterface
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function supports(string $channel): bool
    {
        return $channel === Channel::SMS;
    }

    public function getName(): string
    {
        return 'twilio';
    }

    public function send(Message $message): void
    {
        $sid = $_ENV['TWILIO_ACCOUNT_SID'] ?? '';
        $token = $_ENV['TWILIO_AUTH_TOKEN'] ?? '';
        $from = $_ENV['TWILIO_FROM'] ?? '';
        if ($sid === '' || $token === '' || $from === '') {
            throw new ProviderException('Twilio credentials not configured');
        }

        $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $sid);
        try {
            $response = $this->httpClient->request('POST', $url, [
                'auth_basic' => [$sid, $token],
                'body' => [
                    'From' => $from,
                    'To' => $message->getRecipient(),
                    'Body' => $message->getContent(),
                ],
            ]);
            $status = $response->getStatusCode();
            if ($status < 200 || $status >= 300) {
                throw new ProviderException('Twilio send failed with status ' . $status);
            }
        } catch (\Throwable $e) {
            throw new ProviderException('Twilio send failed: ' . $e->getMessage(), 0, $e);
        }
    }
}


