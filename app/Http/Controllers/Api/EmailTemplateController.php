<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmailSetting;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class EmailTemplateController extends Controller
{
    public function getSettings()
    {
        $settings = EmailSetting::first();
        if (!$settings) {
            $settings = [
                'logo_url' => 'https://cdnteamvora.center.biz.id/teamvora/icon.png',
                'primary_color' => '#0284c7',
                'footer_text' => '© ' . date('Y') . ' TeamVora. All rights reserved.',
            ];
        }
        return response()->json(['data' => $settings]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'primary_color' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = EmailSetting::first() ?? new EmailSetting();

        if ($request->hasFile('logo')) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $r2Disk */
            $r2Disk = Storage::disk('r2');
            $path = $request->file('logo')->store('email-logos', 'r2');
            $settings->logo_url = $r2Disk->url($path);
        }

        if ($request->has('primary_color')) {
            $settings->primary_color = $request->primary_color;
        }

        if ($request->has('footer_text')) {
            $settings->footer_text = $request->footer_text;
        }

        $settings->save();

        return response()->json([
            'message' => 'Pengaturan email berhasil disimpan.',
            'data' => $settings
        ]);
    }

    public function getPreview()
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        // Generate a fake notification html preview using a mock invitation
        $invitation = new TeamInvitation([
            'token' => 'preview-token',
            'email' => 'preview@example.com',
            'role' => 'member',
            'expires_at' => now()->addDays(7),
        ]);
        $invitation->team = $authUser?->team ?? new Team(['name' => 'Preview Team']);
        $invitation->invitedBy = $authUser ?? new User(['name' => 'Admin']);

        $notification = new TeamInvitationNotification($invitation);
        $message = $notification->toMail($authUser ?? new User());

        $html = $message->render();

        return response($html)->header('Content-Type', 'text/html');
    }
}
