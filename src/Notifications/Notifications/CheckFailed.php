<?php

namespace Spatie\ServerMonitor\Notifications\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\ServerMonitor\Events\CheckFailed as CheckFailedEvent;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Notifications\BaseNotification;

class CheckFailed extends BaseNotification
{
    /** @var \Spatie\ServerMonitor\Events\CheckWarning */
    public $event;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject($this->getSubject())
            ->line($this->getMessageText());
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getSubject())
                    ->content($this->getMessageText())
                    ->fallback($this->getMessageText())
                    ->timestamp(Carbon::now());
            });
    }

    public function setEvent(CheckFailedEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    public function shouldSend(): bool
    {
        if (! $this->getCheck()->hasStatus(CheckStatus::FAILED)) {
            return false;
        }

        return ! $this->getCheck()->isThrottlingFailedNotifications();
    }
}
