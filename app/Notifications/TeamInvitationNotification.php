<?php

namespace App\Notifications;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TeamInvitationNotification extends Notification
{

    public TeamInvitation $invitation;

    public function __construct(TeamInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function via(object $notifiable): array
    {
        return $notifiable instanceof \App\Models\User ? ['mail', 'database'] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $acceptUrl = "{$frontendUrl}/invite/{$this->invitation->token}";

        $settings = \App\Models\Setting::all()->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key'));
            
        $settingsData = [];
        $settingsData['email_logo_url'] = $settings['general']['logo_url'] ?? null;
        $settingsData['email_sender_name'] = $settings['email']['email_sender_name'] ?? 'TeamVora';
        $settingsData['email_reply_to'] = $settings['email']['email_reply_to'] ?? null;
        $settingsData['email_primary_color'] = $settings['email']['email_primary_color'] ?? '#2563eb';
        $settingsData['email_button_color'] = $settings['email']['email_button_color'] ?? '#ffffff';
        $settingsData['email_footer_text'] = $settings['email']['email_footer_text'] ?? 'TeamVora. Hak Cipta Dilindungi.';

        $message = (new MailMessage)
            ->subject('Undangan Bergabung Tim: ' . $this->invitation->team->name)
            ->view('emails.invitation', [
                'invitation' => $this->invitation,
                'acceptUrl' => $acceptUrl,
                'settings' => $settingsData
            ]);

        if (!empty($settingsData['email_reply_to'])) {
            $message->replyTo($settingsData['email_reply_to']);
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'team_invitation',
            'title' => 'Undangan Tim',
            'message' => 'Anda diundang bergabung ke tim: ' . $this->invitation->team->name,
            'url' => "/invitations/{$this->invitation->token}",
        ];
    }
}
