<?php

namespace App\NotificationPublisher\Infrastructure\Sms;

use App\NotificationPublisher\Domain\Channel;
use App\NotificationPublisher\Domain\Message;
use App\NotificationPublisher\Domain\NotificationProviderInterface;
use App\NotificationPublisher\Domain\ProviderException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TextbeltSmsProvider implements NotificationProviderInterface
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
        return 'textbelt';
    }

    public function send(Message $message): void
    {
        $apiKey = $_ENV['TEXTBELT_API_KEY'] ?? 'textbelt';
        try {
            $response = $this->httpClient->request('POST', 'https://textbelt.com/text', [
                'body' => [
                    'phone' => $message->getRecipient(),
                    'message' => $message->getContent(),
                    'key' => $apiKey,
                ],
            ]);
            $status = $response->getStatusCode();
            if ($status < 200 || $status >= 300) {
                throw new ProviderException('Textbelt send failed with status ' . $status);
            }
        } catch (\Throwable $e) {
            throw new ProviderException('Textbelt send failed: ' . $e->getMessage(), 0, $e);
        }
    }
}


