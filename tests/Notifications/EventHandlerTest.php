<?php

declare(strict_types=1);

namespace Iraecio\Updater\Tests\Notifications;

use Illuminate\Support\Facades\Notification;
use Iraecio\Updater\Events\UpdateFailed;
use Iraecio\Updater\Models\Release;
use Iraecio\Updater\Notifications\Notifiable;
use Iraecio\Updater\Notifications\Notifications\UpdateFailed as UpdateFailedNotification;
use Iraecio\Updater\Tests\TestCase;

class EventHandlerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_by_default_when_update_failed()
    {
        $this->fireUpdateFailedEvent();

        Notification::assertSentTo(new Notifiable(), UpdateFailedNotification::class);
    }

    /**
     * @test
     *
     * @dataProvider channelProvider
     *
     * @param array $expectedChannels
     */
    public function it_will_send_a_notification_via_the_configured_notification_channels(array $expectedChannels)
    {
        config()->set('self-update.notifications.notifications.'.UpdateFailedNotification::class, $expectedChannels);

        $this->fireUpdateFailedEvent();

        Notification::assertSentTo(new Notifiable(), UpdateFailedNotification::class, function ($notification, $usedChannels) use ($expectedChannels) {
            return $expectedChannels == $usedChannels;
        });
    }

    public function channelProvider()
    {
        return [
            [[]],
            [['mail']],
        ];
    }

    protected function fireUpdateFailedEvent()
    {
        $release = resolve(Release::class);

        event(new UpdateFailed($release));
    }
}
