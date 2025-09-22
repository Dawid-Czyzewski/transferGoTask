<?php

namespace App\Tests\Unit;

use App\NotificationPublisher\Application\Config\NotificationConfig;
use App\NotificationPublisher\Application\NotificationService;
use App\NotificationPublisher\Application\Usage\NotificationUsageTracker;
use App\NotificationPublisher\Domain\Channel;
use App\NotificationPublisher\Domain\Message;
use App\NotificationPublisher\Domain\NotificationProviderInterface;
use App\NotificationPublisher\Domain\ProviderException;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    public function testFailoverSkipsFirstProviderOnFailure(): void
    {
        $first = new class implements NotificationProviderInterface {
            public function supports(string $channel): bool { return $channel === Channel::EMAIL; }
            public function getName(): string { return 'gmail'; }
            public function send(Message $message): void { throw new ProviderException('down'); }
        };
        $secondCalled = false;
        $second = new class($secondCalled) implements NotificationProviderInterface {
            private $calledRef;
            public function __construct(& $calledRef) { $this->calledRef = & $calledRef; }
            public function supports(string $channel): bool { return $channel === Channel::EMAIL; }
            public function getName(): string { return 'mailtrap'; }
            public function send(Message $message): void { $this->calledRef = true; }
        };

        $_ENV['CHANNEL_EMAIL_ENABLED'] = '1';
        $_ENV['EMAIL_PROVIDERS'] = 'gmail,mailtrap';
        $service = new NotificationService([$first, $second], new NotificationConfig(), new NotificationUsageTracker(sys_get_temp_dir().'/notifications_test.csv'));
        $service->send('u1', [Channel::EMAIL], 'to@example.com', 's', 'c');
        $this->assertTrue($secondCalled);
    }

    public function testProviderOrderRespected(): void
    {
        $calls = [];
        $p1 = new class($calls) implements NotificationProviderInterface {
            private $calls;
            public function __construct(& $calls) { $this->calls = & $calls; }
            public function supports(string $channel): bool { return $channel === Channel::EMAIL; }
            public function getName(): string { return 'a'; }
            public function send(Message $message): void { $this->calls[] = 'a'; }
        };
        $p2 = new class($calls) implements NotificationProviderInterface {
            private $calls;
            public function __construct(& $calls) { $this->calls = & $calls; }
            public function supports(string $channel): bool { return $channel === Channel::EMAIL; }
            public function getName(): string { return 'b'; }
            public function send(Message $message): void { $this->calls[] = 'b'; throw new ProviderException('x'); }
        };

        $_ENV['CHANNEL_EMAIL_ENABLED'] = '1';
        $_ENV['EMAIL_PROVIDERS'] = 'b,a';
        $service = new NotificationService([$p1, $p2], new NotificationConfig(), new NotificationUsageTracker(sys_get_temp_dir().'/notifications_test.csv'));
        $service->send('u1', [Channel::EMAIL], 'to@example.com', 's', 'c');
        $this->assertSame(['b','a'], $calls);
    }
}


