<?php

namespace Spatie\ServerMonitor\Notifications\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\ServerMonitor\Events\CheckRestored as CheckRestoredEvent;
use Spatie\ServerMonitor\Models\Enums\CheckStatus;
use Spatie\ServerMonitor\Notifications\BaseNotification;

class CheckRestored extends BaseNotification
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
            ->success()
            ->subject($this->getSubject())
            ->line($this->getMessageText());
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getSubject())
                    ->content($this->getMessageText())
                    ->fallback($this->getMessageText())
                    ->timestamp(Carbon::now());
            });
    }

    public function setEvent(CheckRestoredEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    public function shouldSend(): bool
    {
        return $this->getCheck()->hasStatus(CheckStatus::SUCCESS);
    }
}
