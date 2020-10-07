<?php

declare(strict_types=1);

namespace Iraecio\Updater\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Iraecio\Updater\Events\UpdateSucceeded as UpdateSucceededEvent;
use Iraecio\Updater\Notifications\BaseNotification;

final class UpdateSucceeded extends BaseNotification
{
    /**
     * @var UpdateSucceededEvent
     */
    protected $event;

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->from(config('self-update.notifications.mail.from.address', config('mail.from.address')), config('self-update.notifications.mail.from.name', config('mail.from.name')))
            ->subject(config('app.name').': Update succeeded');
    }

    public function setEvent(UpdateSucceededEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
