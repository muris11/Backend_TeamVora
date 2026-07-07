<?php

use App\Console\Commands\GenerateRecurringBills;
use App\Console\Commands\RemindBillItems;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GenerateRecurringBills::class)->dailyAt('02:00');
Schedule::command(RemindBillItems::class)->dailyAt('07:00');

// Delete chat messages older than a week to save space
Schedule::call(function () {
    $oldMessages = \App\Models\Message::where('created_at', '<', now()->subDays(7))->get();
    
    foreach ($oldMessages as $message) {
        if ($message->media_id) {
            $media = \App\Models\TeamMedia::find($message->media_id);
            if ($media) {
                // Delete from R2
                \Illuminate\Support\Facades\Storage::disk('r2')->delete($media->file_path);
                $media->delete();
            }
        } elseif ($message->attachment_path) {
            // Fallback for non-media_id attachments if any
            \Illuminate\Support\Facades\Storage::disk('r2')->delete($message->attachment_path);
        }
        $message->delete();
    }
})->dailyAt('03:00');
