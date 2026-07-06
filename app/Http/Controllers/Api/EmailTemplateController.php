<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailSetting;
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
            $path = $request->file('logo')->store('email-logos', 'r2');
            $settings->logo_url = Storage::disk('r2')->url($path);
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
        // Generate a fake notification html preview
        $notification = new TeamInvitationNotification('Preview Team', 'superadmin', 'http://localhost:3000/invite/preview');
        $message = $notification->toMail(auth()->user() ?? new \App\Models\User());
        
        $html = $message->render();

        return response($html)->header('Content-Type', 'text/html');
    }
}
