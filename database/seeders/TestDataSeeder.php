<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\CashBook;
use App\Models\DailyLog;
use App\Models\SplitBill;
use App\Models\BillItem;
use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Teams
        $teamAlpha = Team::firstOrCreate(
            ['slug' => 'tim-alpha'],
            ['name' => 'Tim Alpha', 'description' => 'Tim Development TeamVora']
        );
        $teamBeta = Team::firstOrCreate(
            ['slug' => 'tim-beta'],
            ['name' => 'Tim Beta', 'description' => 'Tim Marketing TeamVora']
        );

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@teamvora.web.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // Team Leaders
        $leader1 = User::firstOrCreate(
            ['email' => 'budi@teamvora.web.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'team_leader',
                'team_id' => $teamAlpha->id,
                'email_verified_at' => now(),
            ]
        );
        $leader1->syncRoles(['team_leader']);

        $leader2 = User::firstOrCreate(
            ['email' => 'sari@teamvora.web.id'],
            [
                'name' => 'Sari Dewi',
                'password' => Hash::make('password'),
                'role' => 'team_leader',
                'team_id' => $teamBeta->id,
                'email_verified_at' => now(),
            ]
        );
        $leader2->syncRoles(['team_leader']);

        // Members
        $members = [
            ['name' => 'Andi Pratama', 'email' => 'andi@teamvora.web.id', 'team_id' => $teamAlpha->id],
            ['name' => 'Maya Putri', 'email' => 'maya@teamvora.web.id', 'team_id' => $teamAlpha->id],
            ['name' => 'Rizky Ramadhan', 'email' => 'rizky@teamvora.web.id', 'team_id' => $teamAlpha->id],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@teamvora.web.id', 'team_id' => $teamBeta->id],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar@teamvora.web.id', 'team_id' => $teamBeta->id],
            ['name' => 'Lisa Anggraini', 'email' => 'lisa@teamvora.web.id', 'team_id' => $teamBeta->id],
        ];

        $createdMembers = [];
        foreach ($members as $m) {
            $user = User::firstOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => Hash::make('password'),
                    'role' => 'member',
                    'team_id' => $m['team_id'],
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['member']);
            $createdMembers[] = $user;
        }

        // Blogs
        $blogData = [
            [
                'title' => 'Cara Mengelola Tim Remote dengan Efektif di Era Digital',
                'excerpt' => 'Panduan lengkap mengelola tim remote agar tetap produktif dan terhubung.',
                'content' => '<p>Era digital membawa perubahan besar dalam cara kita bekerja. Tim remote menjadi semakin umum, namun tantangan dalam pengelolaannya juga tidak sedikit.</p><h3>1. Komunikasi yang Jelas</h3><p>Pastikan setiap anggota tim memahami ekspektasi dan target yang harus dicapai.</p><h3>2. Gunakan Tools yang Tepat</h3><p>Manfaatkan platform seperti TeamVora untuk mengelola tugas, dokumen, dan komunikasi tim.</p><h3>3. Rutin Check-in</h3><p>Jadwalkan meeting rutin untuk memastikan semua orang selaras.</p>',
                'status' => 'published',
                'author_id' => $superAdmin->id,
                'team_id' => null,
            ],
            [
                'title' => 'Pentingnya Transparansi Keuangan dalam Organisasi',
                'excerpt' => 'Bagaimana transparansi keuangan membangun kepercayaan dalam tim.',
                'content' => '<p>Transparansi keuangan adalah kunci kepercayaan dalam setiap organisasi. Ketika semua anggota tim memiliki akses informasi keuangan yang jelas, kolaborasi menjadi lebih baik.</p><h3>Manfaat Transparansi Keuangan</h3><ul><li>Membangun kepercayaan antar anggota tim</li><li>Mengurangi konflik terkait pembagian biaya</li><li>Meningkatkan akuntabilitas</li></ul><p>Dengan fitur Split Bill dan Cash Book di TeamVora, transparansi keuangan tim dapat diwujudkan dengan mudah.</p>',
                'status' => 'published',
                'author_id' => $superAdmin->id,
                'team_id' => null,
            ],
            [
                'title' => 'Tips Mengoptimalkan Daily Log untuk Evaluasi Kinerja',
                'excerpt' => 'Cara membuat daily log yang efektif untuk tracking produktivitas.',
                'content' => '<p>Daily log adalah alat yang powerful untuk tracking produktivitas harian. Berikut tips untuk mengoptimalkannya:</p><h3>1. Konsisten Menulis</h3><p>Buat kebiasaan menulis log setiap hari di waktu yang sama.</p><h3>2. Spesifik dan Terukur</h3><p>Tulis pencapaian spesifik, bukan sekadar "bekerja".</p><h3>3. Review Mingguan</h3><p>Evaluasi log mingguan untuk melihat pola produktivitas.</p>',
                'status' => 'published',
                'author_id' => $leader1->id,
                'team_id' => $teamAlpha->id,
            ],
            [
                'title' => 'Update Fitur Terbaru: Realtime Notifications',
                'excerpt' => 'Kenali fitur notifikasi real-time terbaru di TeamVora.',
                'content' => '<p>Kami dengan bangga mengumumkan fitur terbaru: Realtime Notifications! Sekarang Anda akan mendapatkan notifikasi langsung saat ada aktivitas penting di tim Anda.</p><h3>Fitur Yang Tersedia</h3><ul><li>Notifikasi tugas baru</li><li>Update status tagihan</li><li>Pengingat jatuh tempo</li><li>Aktivitas harian tim</li></ul><p>Update ke versi terbaru untuk menikmati fitur ini!</p>',
                'status' => 'published',
                'author_id' => $superAdmin->id,
                'team_id' => null,
            ],
            [
                'title' => 'Panduan Memulai dengan TeamVora untuk Pemula',
                'excerpt' => 'Step-by-step guide menggunakan TeamVora dari nol.',
                'content' => '<p>Baru pertama kali menggunakan TeamVora? Panduan ini akan membantu Anda memulai.</p><h3>Langkah 1: Buat Akun</h3><p>Daftar di teamvora.web.id dan buat akun baru.</p><h3>Langkah 2: Buat atau Gabung Tim</h3><p>Buat tim baru atau terima undangan dari team leader.</p><h3>Langkah 3: Mulai Bekerja</h3><p>Buat tugas, catat pengeluaran, dan mulai berkolaborasi!</p>',
                'status' => 'draft',
                'author_id' => $leader2->id,
                'team_id' => $teamBeta->id,
            ],
        ];

        foreach ($blogData as $b) {
            Blog::create(array_merge($b, [
                'slug' => Str::slug($b['title']),
                'published_at' => $b['status'] === 'published' ? now()->subDays(rand(1, 30)) : null,
            ]));
        }

        // Cash Books
        $cashData = [
            ['type' => 'in', 'amount' => 5000000, 'title' => 'Sponsor Event Tech', 'description' => 'Sponsor dari acara tech conference', 'created_by' => $superAdmin->id, 'team_id' => null],
            ['type' => 'in', 'amount' => 2500000, 'title' => 'Donasi Komunitas', 'description' => 'Donasi dari komunitas developer', 'created_by' => $superAdmin->id, 'team_id' => null],
            ['type' => 'out', 'amount' => 1500000, 'title' => 'Belanja Peralatan Kantor', 'description' => 'Monitor dan keyboard baru', 'created_by' => $leader1->id, 'team_id' => $teamAlpha->id],
            ['type' => 'out', 'amount' => 800000, 'title' => 'Langganan Tools', 'description' => 'GitHub, Figma, dll', 'created_by' => $leader1->id, 'team_id' => $teamAlpha->id],
            ['type' => 'in', 'amount' => 3000000, 'title' => 'Revenue Proyek', 'description' => 'Pembayaran dari klien proyek web', 'created_by' => $leader2->id, 'team_id' => $teamBeta->id],
            ['type' => 'out', 'amount' => 1200000, 'title' => 'Biaya Marketing', 'description' => 'Iklan social media', 'created_by' => $leader2->id, 'team_id' => $teamBeta->id],
        ];

        foreach ($cashData as $c) {
            CashBook::create(array_merge($c, [
                'date' => now()->subDays(rand(1, 30)),
                'description' => $c['description'] ?? '',
            ]));
        }

        // Tasks
        $taskData = [
            ['title' => 'Setup CI/CD Pipeline', 'description' => 'Konfigurasi GitHub Actions untuk auto deploy', 'priority' => 'high', 'status' => 'in_progress', 'assignee_id' => $createdMembers[0]->id, 'creator_id' => $leader1->id, 'team_id' => $teamAlpha->id],
            ['title' => 'Design UI Dashboard', 'description' => 'Buat mockup dashboard admin', 'priority' => 'medium', 'status' => 'todo', 'assignee_id' => $createdMembers[1]->id, 'creator_id' => $leader1->id, 'team_id' => $teamAlpha->id],
            ['title' => 'Fix Bug Login OAuth', 'description' => 'Error 403 saat login dengan Google', 'priority' => 'high', 'status' => 'done', 'assignee_id' => $createdMembers[2]->id, 'creator_id' => $leader1->id, 'team_id' => $teamAlpha->id],
            ['title' => 'Buat Content Calendar', 'description' => 'Rencana konten social media bulan depan', 'priority' => 'medium', 'status' => 'in_progress', 'assignee_id' => $createdMembers[3]->id, 'creator_id' => $leader2->id, 'team_id' => $teamBeta->id],
            ['title' => 'Analisis Kompetitor', 'description' => 'Riset fitur dan pricing kompetitor', 'priority' => 'low', 'status' => 'todo', 'assignee_id' => $createdMembers[4]->id, 'creator_id' => $leader2->id, 'team_id' => $teamBeta->id],
            ['title' => 'Update Landing Page', 'description' => 'Refresh copy dan visual landing page', 'priority' => 'medium', 'status' => 'todo', 'assignee_id' => $createdMembers[5]->id, 'creator_id' => $leader2->id, 'team_id' => $teamBeta->id],
        ];

        foreach ($taskData as $t) {
            Task::create(array_merge($t, [
                'due_date' => now()->addDays(rand(1, 14)),
            ]));
        }

        // Split Bills
        $bill1 = SplitBill::create([
            'title' => 'Tagihan Internet Bulanan',
            'description' => 'Biaya internet bulan Juni',
            'total_amount' => 500000,
            'due_date' => now()->addDays(7),
            'status' => 'active',
            'creator_id' => $leader1->id,
            'team_id' => $teamAlpha->id,
        ]);

        BillItem::create(['split_bill_id' => $bill1->id, 'user_id' => $leader1->id, 'amount' => 150000, 'status' => 'paid']);
        BillItem::create(['split_bill_id' => $bill1->id, 'user_id' => $createdMembers[0]->id, 'amount' => 100000, 'status' => 'pending_verification']);
        BillItem::create(['split_bill_id' => $bill1->id, 'user_id' => $createdMembers[1]->id, 'amount' => 100000, 'status' => 'unpaid']);
        BillItem::create(['split_bill_id' => $bill1->id, 'user_id' => $createdMembers[2]->id, 'amount' => 150000, 'status' => 'unpaid']);

        $bill2 = SplitBill::create([
            'title' => 'Makan Bersama Tim',
            'description' => 'Makan malam after work',
            'total_amount' => 350000,
            'due_date' => now()->addDays(3),
            'status' => 'active',
            'creator_id' => $leader2->id,
            'team_id' => $teamBeta->id,
        ]);

        BillItem::create(['split_bill_id' => $bill2->id, 'user_id' => $leader2->id, 'amount' => 75000, 'status' => 'paid']);
        BillItem::create(['split_bill_id' => $bill2->id, 'user_id' => $createdMembers[3]->id, 'amount' => 75000, 'status' => 'paid']);
        BillItem::create(['split_bill_id' => $bill2->id, 'user_id' => $createdMembers[4]->id, 'amount' => 75000, 'status' => 'unpaid']);
        BillItem::create(['split_bill_id' => $bill2->id, 'user_id' => $createdMembers[5]->id, 'amount' => 125000, 'status' => 'unpaid']);

        // Daily Logs
        for ($i = 0; $i < 5; $i++) {
            DailyLog::create([
                'user_id' => $createdMembers[array_rand($createdMembers)]->id,
                'team_id' => $teamAlpha->id,
                'log_date' => now()->subDays($i),
                'title' => 'Log Hari ke-' . ($i + 1),
                'content' => 'Hari ini saya menyelesaikan beberapa tugas penting termasuk code review, debugging, dan meeting dengan klien.',
            ]);
        }

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('  Super Admin: admin@teamvora.web.id / password');
        $this->command->info('  Team Leader: budi@teamvora.web.id / password');
        $this->command->info('  Member: andi@teamvora.web.id / password');
    }
}
