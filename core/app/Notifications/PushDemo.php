<?php

namespace App\Notifications;

use App\BasicExtra;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class PushDemo extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $buttonText;
    public $buttonURL;
    public $logId;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title, $message, $buttonText, $buttonURL, $logId = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->buttonText = $buttonText;
        $this->buttonURL = $buttonURL;
        $this->logId = $logId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        $bex = BasicExtra::firstOrFail();

        // Route the click through a tracking redirect so the admin's
        // "notifications opened" stat means something, then on to the
        // real destination.
        $clickUrl = $this->logId
            ? route('push.track', ['log' => $this->logId, 'to' => $this->buttonURL])
            : $this->buttonURL;

        $push = (new WebPushMessage)
                ->title($this->title)
                ->icon(FRONT_IMG_PATH . $bex->push_notification_icon)
                ->action($this->buttonText, $clickUrl);

        if (!empty($this->message)) {
            $push = $push->body($this->message);
        }

        return $push;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
