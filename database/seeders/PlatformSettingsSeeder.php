<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // general
            ['key' => 'site_name',    'value' => 'TeamVora',    'group' => 'general', 'type' => 'string'],
            ['key' => 'tagline',      'value' => 'Satu platform untuk seluruh operasional tim Anda.', 'group' => 'general', 'type' => 'string'],
            ['key' => 'favicon_url',  'value' => '/icon.png',   'group' => 'general', 'type' => 'string'],
            ['key' => 'logo_url',     'value' => '/icon.png',   'group' => 'general', 'type' => 'string'],

            // contact
            ['key' => 'contact_email',   'value' => 'info@teamvora.coded.my.id',   'group' => 'contact', 'type' => 'string'],
            ['key' => 'support_email',   'value' => 'support@teamvora.coded.my.id', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'phone',           'value' => '+62 811 1234 5678',            'group' => 'contact', 'type' => 'string'],
            ['key' => 'address',         'value' => 'Gedung Tech Center Lt. 12, Jl. Sudirman No. 45, Jakarta Selatan 12190', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'office_hours',    'value' => 'Senin - Jumat, 09:00 - 17:00 WIB', 'group' => 'contact', 'type' => 'string'],

            // social
            ['key' => 'twitter_url',  'value' => '', 'group' => 'social', 'type' => 'string'],
            ['key' => 'linkedin_url', 'value' => '', 'group' => 'social', 'type' => 'string'],

            // seo
            ['key' => 'seo_title',       'value' => 'TeamVora - Platform Manajemen Tim', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'seo_description', 'value' => 'Satu platform untuk seluruh operasional tim Anda.', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'seo_keywords',    'value' => 'team management, project management, SaaS', 'group' => 'seo', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
