<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;

class AlertNextActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $canal;

    public $title;

    public $content;

    /**
     * Create a new notification instance.
     */
    public function __construct($canal, $title, $content)
    {
        $this->canal = $canal;
        $this->title = $title;
        $this->content = $content;
        $this->onQueue('notifications');

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {

        info($this->canal);

        return $this->canal;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.alert_next_activity', [
                'notifiable' => $notifiable,
                'content' => $this->content,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'type' => 'Alerte',
            'is_read' => false,
            'user_id' => $notifiable->id,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toFcm($notifiable)
    {
        info('fcm');

        return (new FcmMessage)
            ->content([
                'title' => $this->title,
                'body' => $this->content,
            ])
            ->data([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // for mobile apps
                'message' => $this->content,
            ]);
    }

    /**
     * Determine which connections should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        $fcm = FcmChannel::class;

        return [
            'mail' => 'database',
            'database' => 'sync',
            $fcm => 'database',
        ];
    }
}
