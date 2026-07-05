<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class AdminPlatformController extends Controller
{
    public function getSettings()
    {
        $settings = Setting::all()->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key'));

        return response()->json(['data' => $settings]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $this->guessGroup($key),
                ]
            );
        }

        return response()->json(['message' => 'Platform settings updated successfully']);
    }

    private function guessGroup(string $key): string
    {
        $groups = [
            'general' => ['site_name', 'tagline', 'favicon_url', 'logo_url'],
            'contact' => ['contact_email', 'support_email', 'phone', 'address', 'office_hours'],
            'social'  => ['twitter_url', 'linkedin_url'],
            'email'   => [
                'email_logo_url', 'email_sender_name', 'email_reply_to', 
                'email_button_color', 'email_footer_text', 'email_primary_color'
            ],
            'seo'     => [
                'seo_title', 'seo_description', 'seo_keywords',
                'og_image_url', 'canonical_url', 'twitter_handle',
                'theme_color', 'robots_meta'
            ],
            'marketing' => [
                'hero_title', 'hero_subtitle', 'hero_cta_text', 'hero_cta_link',
                'features_title', 'features', 'testimonials_title', 'testimonials',
                'footer_text', 'nav_links',
                'about_content', 'features_content', 'guide_content',
                'help_content', 'careers_content', 'privacy_content', 'terms_content',
            ],
        ];

        foreach ($groups as $group => $keys) {
            if (in_array($key, $keys)) return $group;
        }

        return 'general';
    }

    public function getSystemStatus()
    {
        return response()->json([
            'data' => [
                'php_version'    => PHP_VERSION,
                'laravel_version'=> app()->version(),
                'db_status'      => $this->checkDatabase(),
                'storage_status' => $this->checkStorage(),
                'cache_status'   => $this->checkCache(),
                'environment'    => app()->environment(),
                'debug_mode'     => config('app.debug'),
                'app_name'       => config('app.name'),
                'app_url'        => config('app.url'),
                'disk_usage'     => $this->getDiskUsage(),
            ]
        ]);
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'disconnected';
        }
    }

    private function checkStorage()
    {
        try {
            Storage::disk('local')->put('test.txt', 'test');
            Storage::disk('local')->delete('test.txt');
            return 'available';
        } catch (\Exception $e) {
            return 'unavailable';
        }
    }

    private function checkCache()
    {
        try {
            cache()->put('test_status', 'test', 1);
            return cache()->get('test_status') === 'test' ? 'active' : 'inactive';
        } catch (\Exception $e) {
            return 'unavailable';
        }
    }

    private function getDiskUsage()
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        return [
            'total'      => $this->formatBytes($total),
            'used'       => $this->formatBytes($used),
            'free'       => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 1),
        ];
    }

    private function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $settings = Setting::all()->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key'));
            
        $settingsData = [];
        $settingsData['email_logo_url'] = $settings['general']['logo_url'] ?? null;
        $settingsData['email_sender_name'] = $settings['email']['email_sender_name'] ?? 'TeamVora';
        $settingsData['email_reply_to'] = $settings['email']['email_reply_to'] ?? null;

        try {
            Mail::send('emails.test', ['settings' => $settingsData], function ($message) use ($request, $settingsData) {
                $message->to($request->email)
                    ->subject('Test Email TeamVora');
                
                if (!empty($settingsData['email_reply_to'])) {
                    $message->replyTo($settingsData['email_reply_to']);
                }
            });

            return response()->json(['message' => 'Email tes berhasil dikirim ke ' . $request->email]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim email tes: ' . $e->getMessage()
            ], 500);
        }
    }
}
