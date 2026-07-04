<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = \App\Models\User::where('email', 'admin@admin.com')->first();
        $lead = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Lead');
        })->first();

        $blogs = [
            [
                'title' => 'Cara Mengelola Tim Remote dengan Efektif di Era Digital',
                'slug' => 'cara-mengelola-tim-remote-dengan-efektif',
                'content' => 'Tim remote membutuhkan manajemen yang berbeda dengan tim konvensional. Gunakan alat komunikasi yang tepat dan tetapkan ekspektasi yang jelas. TeamVora membantu Anda melacak produktivitas melalui fitur Daily Log dan Task Management.',
                'featured_image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c',
                'status' => 'published',
                'author_id' => $superadmin->id ?? 1,
                'team_id' => null, // superadmin post
            ],
            [
                'title' => 'Pentingnya Transparansi Keuangan dalam Organisasi',
                'slug' => 'pentingnya-transparansi-keuangan',
                'content' => 'Transparansi keuangan membangun kepercayaan. Dengan fitur Cash Book dan Split Bill di TeamVora, seluruh anggota tim dapat memantau kas masuk dan keluar secara real-time. Ini sangat penting untuk mencegah fraud.',
                'featured_image' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c',
                'status' => 'published',
                'author_id' => $superadmin->id ?? 1,
                'team_id' => null,
            ],
            [
                'title' => 'Tips Mengoptimalkan Daily Log untuk Evaluasi Kinerja',
                'slug' => 'mengoptimalkan-daily-log-untuk-kinerja',
                'content' => 'Daily log bukan sekadar absen, tapi catatan produktivitas. Pastikan setiap anggota mengisi target harian mereka. Lead dapat mengevaluasi pencapaian harian secara berkala.',
                'featured_image' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4',
                'status' => 'published',
                'author_id' => $lead->id ?? 2,
                'team_id' => $lead->team_id ?? 1,
            ],
            [
                'title' => 'Membuat Tagihan Rutin Secara Otomatis (Recurring Bills)',
                'slug' => 'membuat-tagihan-rutin-otomatis',
                'content' => 'Jangan habiskan waktu menagih anggota secara manual. Gunakan fitur Recurring Bill di TeamVora untuk otomatis generate tagihan kas setiap minggu atau bulan. Tingkatkan kolektibilitas kas tim Anda.',
                'featured_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f',
                'status' => 'draft',
                'author_id' => $lead->id ?? 2,
                'team_id' => $lead->team_id ?? 1,
            ],
            [
                'title' => 'Update Fitur Terbaru: Realtime Notifications',
                'slug' => 'update-fitur-realtime-notifications',
                'content' => 'Kini Anda tidak akan ketinggalan informasi. TeamVora baru saja merilis fitur notifikasi realtime menggunakan Laravel Reverb. Dapatkan update instan saat task selesai atau tagihan dibayar.',
                'featured_image' => 'https://images.unsplash.com/photo-1432821596592-e2c18b78144f',
                'status' => 'published',
                'author_id' => $superadmin->id ?? 1,
                'team_id' => null,
            ]
        ];

        foreach ($blogs as $blog) {
            \App\Models\Blog::updateOrCreate(
                ['slug' => $blog['slug']],
                $blog
            );
        }
    }
}
