<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemplateDrivenNotification extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private EmailTemplate $template;
    private User $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(EmailTemplate $template, User $user)
    {
        $this->template = $template;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $mail_message = new MailMessage();
        return $mail_message
            ->subject($this->template->subject)
            ->view(
                'emails.generic_template',
                [
                    'markup' => $this->template->markup
                ]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'template_code' => $this->template->code,
            'generic_field' => 'test_text'
        ];
    }
}
