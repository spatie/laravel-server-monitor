<?php

namespace Spatie\ServerMonitor\Notifications\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\ServerMonitor\Notifications\BaseNotification;
use Spatie\ServerMonitor\Events\CheckWarning as CheckWarningEvent;

class CheckWarning extends BaseNotification
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
            ->warning()
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getSubject())
                    ->content($this->getMessageText())
                    ->fallback($this->getMessageText())
                    ->timestamp(Carbon::now());
            });
    }

    public function setEvent(CheckWarningEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    public function shouldSend(): bool
    {
        if (! $this->getCheck()->hasStatus(CheckStatus::WARNING)) {
            return false;
        }

        return ! $this->getCheck()->isThrottlingFailedNotifications();
    }
}
