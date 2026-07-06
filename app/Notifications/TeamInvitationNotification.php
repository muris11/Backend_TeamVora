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

        return (new MailMessage)
            ->subject('Undangan Bergabung Tim: ' . $this->invitation->team->name)
            ->line("Anda diundang bergabung ke tim {$this->invitation->team->name}.")
            ->line("Diundang oleh: {$this->invitation->inviter->name}")
            ->action('Terima Undangan', $acceptUrl)
            ->line("Undangan ini berlaku hingga {$this->invitation->expires_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y H:i')} WIB.")
            ->line('Jika Anda tidak merasa diundang, abaikan email ini.');
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
