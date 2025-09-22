<?php

namespace App\NotificationPublisher\Application;

use App\NotificationPublisher\Application\Config\NotificationConfig;
use App\NotificationPublisher\Application\Usage\NotificationUsageTracker;
use App\NotificationPublisher\Domain\Channel;
use App\NotificationPublisher\Domain\Message;
use App\NotificationPublisher\Domain\NotificationProviderInterface;
use App\NotificationPublisher\Domain\ProviderException;

class NotificationService
{
    /** @var NotificationProviderInterface[] */
    private array $providers;
    private NotificationConfig $config;
    private NotificationUsageTracker $usageTracker;

    /** @param iterable<NotificationProviderInterface> $providers */
    public function __construct(iterable $providers, NotificationConfig $config, NotificationUsageTracker $usageTracker)
    {
        $this->providers = [];
        foreach ($providers as $provider) {
            $this->providers[] = $provider;
        }
        $this->config = $config;
        $this->usageTracker = $usageTracker;
    }

    /**
     * @param array<int,string> $channels
     */
    public function send(string $userId, array $channels, string $recipient, string $subject, string $content): void
    {
        foreach ($channels as $channel) {
            if (!Channel::isSupported($channel)) {
                continue;
            }
            if (!$this->config->isChannelEnabled($channel)) {
                continue;
            }
            $message = new Message($userId, $channel, $recipient, $subject, $content);
            $this->sendWithFailover($message);
        }
    }

    private function sendWithFailover(Message $message): void
    {
        $providersOrder = $this->config->providersFor($message->getChannel());
        $candidates = array_values(array_filter($this->providers, function (NotificationProviderInterface $p) use ($message, $providersOrder) {
            if (!$p->supports($message->getChannel())) {
                return false;
            }
            return empty($providersOrder) || in_array($p->getName(), $providersOrder, true);
        }));

        if (!empty($providersOrder)) {
            usort($candidates, function (NotificationProviderInterface $a, NotificationProviderInterface $b) use ($providersOrder) {
                return array_search($a->getName(), $providersOrder, true) <=> array_search($b->getName(), $providersOrder, true);
            });
        }

        $lastException = null;
        foreach ($candidates as $provider) {
            try {
                $provider->send($message);
                $this->usageTracker->trackSent($message, $provider->getName());
                return;
            } catch (ProviderException $ex) {
                $lastException = $ex;
                continue;
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }
    }
}


