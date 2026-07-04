<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            'seo'     => ['seo_title', 'seo_description', 'seo_keywords'],
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

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
