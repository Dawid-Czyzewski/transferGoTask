<?php

namespace App\NotificationPublisher\UserInterface\Http;

use App\NotificationPublisher\Application\Message\SendNotificationMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SendNotificationController
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[Route(path: '/notify', name: 'notify', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $userId = (string)($payload['userId'] ?? '');
        $channels = (array)($payload['channels'] ?? []);
        $recipient = (string)($payload['recipient'] ?? '');
        $subject = (string)($payload['subject'] ?? '');
        $content = (string)($payload['content'] ?? '');

        if ($userId === '' || $recipient === '' || empty($channels)) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $this->bus->dispatch(new SendNotificationMessage($userId, $channels, $recipient, $subject, $content));

        return new JsonResponse(['status' => 'queued']);
    }
}


