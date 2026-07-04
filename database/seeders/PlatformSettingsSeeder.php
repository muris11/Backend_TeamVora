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

            // marketing
            ['key' => 'landing_content', 'value' => json_encode([
                'hero_title' => 'Satu Platform untuk Seluruh Operasional Tim Anda',
                'hero_subtitle' => 'Tingkatkan produktivitas dan kolaborasi tim dengan solusi all-in-one dari TeamVora.',
                'features' => [
                    ['title' => 'Komunikasi Tim', 'description' => 'Diskusi real-time dan berbagi file dalam satu tempat.', 'icon' => 'MessageCircle', 'color' => '#3b82f6'],
                    ['title' => 'Manajemen Tugas', 'description' => 'Lacak progress dan tetapkan prioritas dengan mudah.', 'icon' => 'CheckCircle2', 'color' => '#10b981'],
                    ['title' => 'Absensi & Kehadiran', 'description' => 'Sistem absensi cerdas berbasis lokasi dan waktu.', 'icon' => 'Clock', 'color' => '#f59e0b'],
                    ['title' => 'Split Bill & Kas', 'description' => 'Kelola patungan dan kas tim secara transparan.', 'icon' => 'Wallet', 'color' => '#8b5cf6'],
                ],
                'stats' => [
                    ['label' => 'Tim Aktif', 'value' => '10k+'],
                    ['label' => 'Tugas Selesai', 'value' => '1M+'],
                    ['label' => 'Uptime', 'value' => '99.9%'],
                ],
            ]), 'group' => 'marketing', 'type' => 'string'],

            ['key' => 'features_content', 'value' => json_encode([
                'title' => 'Fitur Lengkap untuk Tim Modern',
                'subtitle' => 'Semua alat yang Anda butuhkan untuk mengelola tim, proyek, dan keuangan dalam satu platform terpadu.',
                'features' => [
                    ['title' => 'Komunikasi Real-time', 'description' => 'Chat grup dan direct message terintegrasi.', 'icon' => 'MessageCircle', 'color' => '#3b82f6'],
                    ['title' => 'Kanban Board', 'description' => 'Visualisasi alur kerja tim Anda.', 'icon' => 'Layout', 'color' => '#6366f1'],
                    ['title' => 'Absensi Geolocation', 'description' => 'Catat kehadiran dengan akurasi lokasi.', 'icon' => 'MapPin', 'color' => '#10b981'],
                    ['title' => 'Manajemen Cuti', 'description' => 'Sistem pengajuan dan persetujuan otomatis.', 'icon' => 'Briefcase', 'color' => '#f59e0b'],
                    ['title' => 'Laporan Kas', 'description' => 'Transparansi keuangan tim secara realtime.', 'icon' => 'Activity', 'color' => '#8b5cf6'],
                    ['title' => 'Berbagi File', 'description' => 'Simpan dan bagikan dokumen dengan aman.', 'icon' => 'FileText', 'color' => '#ec4899'],
                ],
            ]), 'group' => 'marketing', 'type' => 'string'],

            ['key' => 'about_content', 'value' => json_encode([
                'mission' => 'Memberdayakan tim di seluruh dunia untuk bekerja lebih cerdas, bukan lebih keras.',
                'vision' => 'Menjadi standar global untuk platform kolaborasi dan manajemen operasional tim.',
                'values' => [
                    ['title' => 'Inovasi Berkelanjutan', 'description' => 'Selalu mencari cara yang lebih baik untuk memecahkan masalah kompleks.', 'icon' => 'Zap'],
                    ['title' => 'Transparansi', 'description' => 'Terbuka dalam komunikasi dan pengambilan keputusan di setiap level.', 'icon' => 'Search'],
                    ['title' => 'Keamanan Data', 'description' => 'Menjaga kerahasiaan dan keamanan informasi pengguna sebagai prioritas utama.', 'icon' => 'Shield'],
                    ['title' => 'Fokus pada Pengguna', 'description' => 'Setiap fitur dibangun berdasarkan kebutuhan dan feedback pengguna.', 'icon' => 'Users'],
                ],
                'team' => [
                    ['name' => 'Budi Santoso', 'role' => 'Chief Executive Officer', 'image' => 'https://ui-avatars.com/api/?name=Budi+Santoso&background=random'],
                    ['name' => 'Siti Aminah', 'role' => 'Chief Technology Officer', 'image' => 'https://ui-avatars.com/api/?name=Siti+Aminah&background=random'],
                    ['name' => 'Andi Wijaya', 'role' => 'Head of Product', 'image' => 'https://ui-avatars.com/api/?name=Andi+Wijaya&background=random'],
                ],
            ]), 'group' => 'marketing', 'type' => 'string'],

            ['key' => 'careers_content', 'value' => json_encode([
                'benefits' => [
                    ['name' => 'Waktu Kerja Fleksibel', 'description' => 'Fokus pada hasil, bukan jam kerja. Atur jadwal Anda sendiri.'],
                    ['name' => 'Remote Work', 'description' => 'Bekerja dari mana saja dengan dukungan penuh dari tim.'],
                    ['name' => 'Asuransi Kesehatan', 'description' => 'Perlindungan kesehatan komprehensif untuk Anda dan keluarga.'],
                    ['name' => 'Dana Pengembangan', 'description' => 'Budget tahunan untuk kursus, buku, dan konferensi.'],
                ],
                'openings' => [
                    ['title' => 'Senior Frontend Developer', 'department' => 'Engineering', 'location' => 'Remote', 'type' => 'Full-time', 'description' => 'Berpengalaman dalam React, Next.js, dan Tailwind CSS.'],
                    ['title' => 'Product Designer', 'department' => 'Design', 'location' => 'Jakarta, ID', 'type' => 'Full-time', 'description' => 'Mampu merancang antarmuka pengguna yang intuitif dan menarik.'],
                    ['title' => 'DevOps Engineer', 'department' => 'Engineering', 'location' => 'Remote', 'type' => 'Full-time', 'description' => 'Familiar dengan AWS, Docker, dan CI/CD pipelines.'],
                ],
            ]), 'group' => 'marketing', 'type' => 'string'],

            ['key' => 'help_content', 'value' => json_encode([
                'articles' => [
                    ['title' => 'Hubungi via Email', 'description' => 'Tim dukungan kami siap membantu Anda 24/7 melalui email.', 'icon' => 'Mail', 'action' => 'Kirim Email', 'link' => 'mailto:support@teamvora.coded.my.id'],
                    ['title' => 'Live Chat', 'description' => 'Ngobrol langsung dengan agen dukungan kami pada jam kerja.', 'icon' => 'MessageCircle', 'action' => 'Mulai Chat', 'link' => '#'],
                    ['title' => 'Telepon', 'description' => 'Butuh bantuan mendesak? Hubungi kami langsung.', 'icon' => 'Phone', 'action' => 'Hubungi Kami', 'link' => 'tel:+6281112345678'],
                ],
                'popular_articles' => [
                    'Cara menambahkan anggota tim baru',
                    'Mengatur jadwal shift karyawan',
                    'Integrasi dengan aplikasi pihak ketiga',
                    'Reset password akun administrator',
                ],
            ]), 'group' => 'marketing', 'type' => 'string'],

            ['key' => 'guide_content', 'value' => json_encode([
                'categories' => [
                    ['title' => 'Memulai TeamVora', 'description' => 'Langkah-langkah awal untuk menggunakan TeamVora.', 'icon' => 'Book', 'articles' => ['Cara Mendaftar Akun Baru', 'Mengundang Anggota Tim', 'Pengaturan Profil Pengguna']],
                    ['title' => 'Manajemen Proyek', 'description' => 'Cara mengelola proyek dan tugas dengan efisien.', 'icon' => 'Layout', 'articles' => ['Membuat Proyek Baru', 'Menggunakan Kanban Board', 'Mengatur Tenggat Waktu']],
                    ['title' => 'Administrasi & Keuangan', 'description' => 'Panduan terkait billing, invoice, dan administrasi.', 'icon' => 'Briefcase', 'articles' => ['Cara Upgrade Layanan', 'Mengunduh Invoice Bulanan', 'Membatalkan Langganan']],
                ],
                'faqs' => [
                    ['question' => 'Apakah TeamVora menyediakan uji coba gratis?', 'answer' => 'Ya, kami menyediakan uji coba gratis selama 14 hari dengan akses ke semua fitur premium.'],
                    ['question' => 'Berapa batasan anggota tim yang bisa ditambahkan?', 'answer' => 'Tergantung pada paket langganan Anda. Paket Enterprise tidak memiliki batasan anggota tim.'],
                    ['question' => 'Apakah data saya aman?', 'answer' => 'Kami menggunakan enkripsi end-to-end dan server yang aman untuk melindungi data Anda.'],
                ],
            ]), 'group' => 'marketing', 'type' => 'string'],

            ['key' => 'privacy_content', 'value' => '<h2>Kebijakan Privasi TeamVora</h2><p>Kami sangat menghargai privasi Anda dan berkomitmen untuk melindungi data pribadi pengguna platform kami.</p><h3>1. Informasi yang Kami Kumpulkan</h3><p>Kami mengumpulkan informasi pendaftaran, log aktivitas, dan data operasional tim yang Anda masukkan.</p><h3>2. Penggunaan Data</h3><p>Data Anda hanya digunakan untuk menyediakan layanan TeamVora, meningkatkan kualitas platform, dan berkomunikasi dengan Anda.</p><h3>3. Keamanan</h3><p>Kami menerapkan standar keamanan tinggi, termasuk enkripsi TLS/SSL untuk seluruh transmisi data.</p>', 'group' => 'marketing', 'type' => 'string'],
            
            ['key' => 'terms_content', 'value' => '<h2>Syarat dan Ketentuan Layanan</h2><p>Dengan menggunakan TeamVora, Anda menyetujui syarat dan ketentuan berikut:</p><h3>1. Penggunaan Platform</h3><p>Anda bertanggung jawab penuh atas segala aktivitas yang terjadi di bawah akun Anda.</p><h3>2. Pembayaran dan Langganan</h3><p>Biaya layanan dibayarkan di muka sesuai siklus billing. Tidak ada refund untuk periode yang sudah berjalan.</p><h3>3. Penghentian Layanan</h3><p>Kami berhak menghentikan layanan jika ditemukan pelanggaran terhadap syarat dan ketentuan ini.</p>', 'group' => 'marketing', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
