<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class DefaultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $canal;

    public $title;

    public $content;

    public $file;

    /**
     * Create a new notification instance.
     */
    public function __construct($canal, $title, $content, $file = null)
    {
        $this->canal = $canal;
        $this->title = $title;
        $this->content = $content;
        $this->file = $file;
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->canal;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        info('canal email');
        info($this->file);

        if ($this->file) {
            return (new MailMessage)
                ->subject($this->title)
                ->view('emails.default_notification_template', [
                    'notifiable' => $notifiable,
                    'content' => $this->content,
                ])
                ->attach($this->file);
        }

        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.default_notification_template', [
                'notifiable' => $notifiable,
                'content' => $this->content,
            ]);
    }

    public function toTwilio($notifiable)
    {
        info('twilio');

        return (new TwilioSmsMessage)->content('Votre code est 123456');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        info('Système');

        return [
            'title' => $this->title,
            'content' => $this->content,
            'type' => 'Alerte',
            'is_read' => false,
            'file' => $this->file,
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

        return new FcmMessage(notification: new FcmNotification(
            title: $this->title,
            body: $this->content
        ));
    }

    /**
     * Determine which connections should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        $fcm = FcmChannel::class;
        $tw = TwilioChannel::class;

        return [
            'mail' => 'database',
            'database' => 'sync',
            //  $fcm => 'database',
            // $tw => 'database',
        ];
    }
}
