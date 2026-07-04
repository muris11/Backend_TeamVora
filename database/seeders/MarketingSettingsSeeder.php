<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class MarketingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // ==================
            // LANDING PAGE
            // ==================
            ['key' => 'hero_title', 'value' => 'Satu Platform untuk', 'group' => 'marketing', 'type' => 'string'],
            ['key' => 'hero_subtitle', 'value' => 'Satu platform untuk seluruh operasional tim Anda.', 'group' => 'marketing', 'type' => 'string'],
            ['key' => 'hero_cta_text', 'value' => 'Mulai Gratis Sekarang', 'group' => 'marketing', 'type' => 'string'],
            ['key' => 'hero_cta_link', 'value' => '/register', 'group' => 'marketing', 'type' => 'string'],
            ['key' => 'features_title', 'value' => 'Segala yang Anda butuhkan,', 'group' => 'marketing', 'type' => 'string'],
            ['key' => 'testimonials_title', 'value' => 'Dicintai oleh tim produktif', 'group' => 'marketing', 'type' => 'string'],
            ['key' => 'features', 'value' => json_encode([
                [
                    "title" => "Manajemen Tugas Intuitif",
                    "description" => "Atur, delegasikan, dan pantau tugas dengan tampilan Kanban atau List yang sangat responsif.",
                    "icon" => "CheckSquare"
                ],
                [
                    "title" => "Transparansi Keuangan",
                    "description" => "Lacak pengeluaran dan pemasukan proyek secara real-time.",
                    "icon" => "Wallet"
                ],
                [
                    "title" => "Kolaborasi Tim",
                    "description" => "Diskusi terpusat tanpa harus berpindah aplikasi.",
                    "icon" => "Users"
                ],
                [
                    "title" => "Keamanan Enterprise",
                    "description" => "Data Anda dienkripsi dan dicadangkan secara berkala.",
                    "icon" => "Shield"
                ],
                [
                    "title" => "Analitik Mendalam",
                    "description" => "Dapatkan insight visual untuk produktivitas tim.",
                    "icon" => "BarChart3"
                ],
                [
                    "title" => "Kinerja Secepat Kilat",
                    "description" => "Dibangun dengan teknologi modern untuk waktu muat instan.",
                    "icon" => "Zap"
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],
            ['key' => 'testimonials', 'value' => json_encode([
                [
                    "name" => "Budi Santoso",
                    "role" => "CEO, TechCorp",
                    "quote" => "Sangat membantu tim kami!"
                ],
                [
                    "name" => "Siti Aminah",
                    "role" => "Manager, StartupID",
                    "quote" => "Aplikasi yang mudah digunakan dan lengkap."
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],
            ['key' => 'client_logos', 'value' => json_encode([
                "Acme Corp", "GlobalTech", "Nexus", "Starlight", "Omega", "Zeta", "Vertex", "Quantum"
            ]), 'group' => 'marketing', 'type' => 'json'],

            // ==================
            // TENTANG KAMI
            // ==================
            ['key' => 'about_content', 'value' => json_encode([
                'stats' => [
                    ['value' => '10K+', 'label' => 'Tim Aktif', 'icon' => 'Users'],
                    ['value' => '50+', 'label' => 'Negara', 'icon' => 'Globe2'],
                    ['value' => '99.9%', 'label' => 'Uptime', 'icon' => 'Zap'],
                    ['value' => '24/7', 'label' => 'Dukungan', 'icon' => 'HeartHandshake'],
                ],
                'values' => [
                    ['title' => 'Transparansi Penuh', 'description' => 'Kami percaya pada komunikasi terbuka, baik secara internal maupun dengan pengguna kami. Tidak ada biaya tersembunyi, tidak ada kejutan.', 'icon' => 'Target'],
                    ['title' => 'Keamanan Terutama', 'description' => 'Data Anda adalah prioritas utama kami. Kami menerapkan standar keamanan kelas perbankan di setiap lapisan aplikasi.', 'icon' => 'Shield'],
                    ['title' => 'Inovasi Berkelanjutan', 'description' => 'Kami tidak pernah berhenti belajar dan berkembang. Produk kami diperbarui setiap minggu berdasarkan masukan Anda.', 'icon' => 'Trophy'],
                ],
                'team' => [
                    ['name' => 'Andi Saputra', 'role' => 'CEO & Founder', 'initials' => 'AS'],
                    ['name' => 'Budi Pratama', 'role' => 'CTO', 'initials' => 'BP'],
                    ['name' => 'Citra Dewi', 'role' => 'Head of Product', 'initials' => 'CD'],
                    ['name' => 'Dian Novita', 'role' => 'Lead Designer', 'initials' => 'DN'],
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],

            // ==================
            // FITUR
            // ==================
            ['key' => 'features_content', 'value' => json_encode([
                'sections' => [
                    [
                        'id' => 'absensi',
                        'title' => 'Absensi & Manajemen Karyawan',
                        'description' => 'Tinggalkan metode absensi manual yang tidak akurat. Pantau kehadiran, cuti, dan shift dengan mudah.',
                        'icon' => 'CheckCircle2',
                        'color' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                        'points' => [
                            'Pencatatan waktu real-time',
                            'Pengajuan cuti & izin terintegrasi',
                            'Manajemen jadwal shift otomatis',
                            'Laporan absensi komprehensif'
                        ]
                    ],
                    [
                        'id' => 'produktivitas',
                        'title' => 'Produktivitas & Tugas',
                        'description' => 'Pantau pekerjaan setiap anggota tim setiap harinya tanpa harus micromanage berlebihan.',
                        'icon' => 'ListTodo',
                        'color' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                        'points' => [
                            'Daily log dan laporan harian',
                            'Board tugas bergaya Kanban',
                            'Alokasi beban kerja tim',
                            'Monitoring progres otomatis'
                        ]
                    ],
                    [
                        'id' => 'keuangan',
                        'title' => 'Keuangan Terpusat',
                        'description' => 'Catat setiap pengeluaran, pantau buku kas, dan kelola tagihan operasional dalam satu layar.',
                        'icon' => 'Wallet',
                        'color' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                        'points' => [
                            'Buku Kas (Cash Book) transparan',
                            'Manajemen tagihan (Bills)',
                            'Pengingat tagihan berulang (Recurring)',
                            'Rekap pengeluaran otomatis'
                        ]
                    ],
                    [
                        'id' => 'dokumen',
                        'title' => 'Manajemen Media & Dokumen',
                        'description' => 'Simpan semua berkas penting dan media perusahaan di tempat yang aman dan mudah diakses.',
                        'icon' => 'FolderOpen',
                        'color' => 'bg-purple-500/10 text-purple-500 border-purple-500/20',
                        'points' => [
                            'Pusat dokumen perusahaan',
                            'Galeri media (foto/video operasional)',
                            'Kontrol akses berbasis peran (Role-based)',
                            'Keamanan enkripsi standar industri'
                        ]
                    ]
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],

            // ==================
            // PANDUAN
            // ==================
            ['key' => 'guide_content', 'value' => json_encode([
                'categories' => [
                    [
                        'title' => 'Mulai Menggunakan',
                        'icon' => 'Book',
                        'description' => 'Langkah-langkah awal untuk mengatur tim dan akun Anda di TeamVora.',
                        'articles' => [
                            'Cara mendaftarkan perusahaan Anda',
                            'Mengundang anggota tim ke dalam platform',
                            'Mengatur struktur peran dan akses',
                            'Memahami dashboard utama'
                        ]
                    ],
                    [
                        'title' => 'Manajemen Keuangan',
                        'icon' => 'CreditCard',
                        'description' => 'Kelola kas, tagihan, dan pengeluaran secara transparan.',
                        'articles' => [
                            'Mencatat pemasukan dan pengeluaran Kas',
                            'Membuat Split Bill untuk tim',
                            'Cara kerja Recurring Bill otomatis',
                            'Verifikasi pembayaran anggota'
                        ]
                    ],
                    [
                        'title' => 'Produktivitas & Tugas',
                        'icon' => 'FileText',
                        'description' => 'Pantau aktivitas harian dan delegasi tugas anggota.',
                        'articles' => [
                            'Membuat dan mendelegasikan Task',
                            'Mengisi Daily Log dan absensi',
                            'Melihat progres dan produktivitas tim'
                        ]
                    ],
                    [
                        'title' => 'Pengaturan & Keamanan',
                        'icon' => 'Shield',
                        'description' => 'Amankan akun dan kelola pengaturan platform Anda.',
                        'articles' => [
                            'Cara mengganti password',
                            'Memperbarui profil dan avatar',
                            'Mengelola notifikasi email'
                        ]
                    ]
                ],
                'faqs' => [
                    [
                        'question' => 'Bagaimana cara mengubah peran (role) anggota tim?',
                        'answer' => 'Hanya Lead (Ketua Tim) dan Admin yang dapat mengubah peran. Pergi ke halaman Pengaturan Tim, pilih anggota, dan klik opsi "Ubah Role". Anda dapat menjadikannya Treasurer atau Member biasa.'
                    ],
                    [
                        'question' => 'Apakah fitur Notifikasi Real-time berbayar?',
                        'answer' => 'Tidak. Fitur notifikasi real-time kami sudah termasuk dalam platform secara default, sehingga Anda tidak akan kehilangan info penting kapan pun ada aktivitas terbaru.'
                    ],
                    [
                        'question' => 'Bagaimana siklus penagihan Recurring Bill?',
                        'answer' => 'Recurring Bill (Tagihan Berulang) dapat diatur per minggu atau per bulan oleh Treasurer/Lead. Sistem akan secara otomatis menggenerate tagihan ke setiap anggota tim sesuai frekuensi yang ditentukan.'
                    ]
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],

            // ==================
            // PANDUAN (GUIDE)
            // ==================
            ['key' => 'guide_content', 'value' => json_encode([
                'categories' => [
                    [
                        'title' => 'Memulai TeamVora',
                        'icon' => 'Book',
                        'description' => 'Pelajari langkah pertama menggunakan TeamVora untuk tim Anda.',
                        'articles' => [
                            'Cara membuat akun dan profil',
                            'Mengundang anggota tim',
                            'Pengaturan dasar workspace'
                        ]
                    ],
                    [
                        'title' => 'Manajemen Proyek',
                        'icon' => 'FolderKanban',
                        'description' => 'Kelola proyek dan tugas dengan lebih efisien.',
                        'articles' => [
                            'Membuat proyek pertama Anda',
                            'Menambahkan tugas dan deadline',
                            'Menggunakan board Kanban'
                        ]
                    ],
                    [
                        'title' => 'Keuangan & Split Bill',
                        'icon' => 'CreditCard',
                        'description' => 'Panduan lengkap fitur manajemen keuangan.',
                        'articles' => [
                            'Cara menggunakan Split Bill',
                            'Mencatat pengeluaran',
                            'Export laporan keuangan'
                        ]
                    ]
                ],
                'faqs' => [
                    [
                        'question' => 'Apakah TeamVora gratis untuk digunakan?',
                        'answer' => 'TeamVora memiliki paket gratis selamanya untuk tim kecil hingga 5 orang. Untuk fitur lengkap, Anda bisa berlangganan paket Premium.'
                    ],
                    [
                        'question' => 'Bagaimana cara membatalkan langganan?',
                        'answer' => 'Anda dapat membatalkan langganan kapan saja melalui menu Pengaturan > Billing > Batalkan Langganan.'
                    ],
                    [
                        'question' => 'Apakah data saya aman?',
                        'answer' => 'Keamanan data Anda adalah prioritas kami. Kami menggunakan enkripsi standar industri untuk melindungi semua informasi Anda.'
                    ]
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],

            // ==================
            // BANTUAN
            // ==================
            ['key' => 'help_content', 'value' => json_encode([
                'articles' => [
                    [
                        'title' => 'Email Support',
                        'icon' => 'Mail',
                        'description' => 'Kirimkan pertanyaan detail Anda kepada tim support kami.',
                        'action' => 'Kirim Email',
                        'link' => 'mailto:info@teamvora.coded.my.id'
                    ],
                    [
                        'title' => 'Live Chat',
                        'icon' => 'MessageCircle',
                        'description' => 'Ngobrol langsung dengan tim support kami (09:00 - 17:00).',
                        'action' => 'Mulai Chat',
                        'link' => '#'
                    ],
                    [
                        'title' => 'Telepon',
                        'icon' => 'Phone',
                        'description' => 'Hubungi layanan pelanggan untuk kasus darurat.',
                        'action' => 'Hubungi Kami',
                        'link' => 'tel:+628123456789'
                    ]
                ],
                'popular_articles' => [
                    "Cara reset kata sandi",
                    "Panduan mengundang anggota tim baru",
                    "Mengatur integrasi dengan Slack",
                    "Cara export laporan keuangan",
                    "Apa itu Split Bill dan cara menggunakannya?",
                    "Memperbarui profil perusahaan",
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],

            // ==================
            // KARIR
            // ==================
            ['key' => 'careers_content', 'value' => json_encode([
                'benefits' => [
                    ['name' => 'Work from Anywhere (Remote/Hybrid)', 'description' => 'Kami menyediakan fasilitas terbaik agar Anda dapat fokus bekerja dengan nyaman.'],
                    ['name' => 'Asuransi Kesehatan Lengkap', 'description' => 'Kesehatan Anda dan keluarga adalah prioritas utama kami.'],
                    ['name' => 'Tunjangan Peralatan Kerja', 'description' => 'Dapatkan budget untuk laptop dan peralatan kerja yang Anda butuhkan.'],
                    ['name' => 'Program Pengembangan Karir', 'description' => 'Dukungan penuh untuk pelatihan, sertifikasi, dan konferensi.'],
                    ['name' => 'Waktu Kerja Fleksibel', 'description' => 'Atur waktu kerja Anda sendiri selama target tim tercapai.'],
                    ['name' => 'Cuti Berbayar (Paid Time Off)', 'description' => 'Nikmati hari libur berbayar untuk recharge energi Anda.']
                ],
                'openings' => [
                    [
                        'title' => 'Senior Full Stack Engineer',
                        'department' => 'Engineering',
                        'location' => 'Remote',
                        'type' => 'Full-time',
                        'description' => 'Kami mencari Senior Engineer yang berpengalaman dengan Laravel dan Next.js untuk memimpin pengembangan fitur-fitur inti TeamVora.'
                    ],
                    [
                        'title' => 'Product Designer (UI/UX)',
                        'department' => 'Design',
                        'location' => 'Jakarta, Hybrid',
                        'type' => 'Full-time',
                        'description' => 'Bergabung dengan tim desain kami untuk menciptakan antarmuka pengguna yang indah dan mudah digunakan bagi ribuan pengguna B2B.'
                    ],
                    [
                        'title' => 'B2B Sales Representative',
                        'department' => 'Sales',
                        'location' => 'Jakarta, On-site',
                        'type' => 'Full-time',
                        'description' => 'Bantu perusahaan di seluruh Indonesia untuk mentransformasi cara mereka mengelola tim dengan mempresentasikan solusi TeamVora.'
                    ],
                    [
                        'title' => 'Customer Success Specialist',
                        'department' => 'Support',
                        'location' => 'Remote',
                        'type' => 'Full-time',
                        'description' => 'Jadilah jembatan antara produk kami dan kepuasan pelanggan, pastikan mereka mendapatkan manfaat maksimal dari TeamVora.'
                    ]
                ]
            ]), 'group' => 'marketing', 'type' => 'json'],

            // ==================
            // PRIVASI
            // ==================
            ['key' => 'privacy_content', 'value' => '<h2>1. Pendahuluan</h2>
<p>Di TeamVora, kami sangat menjaga privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, membagikan, dan melindungi informasi pribadi Anda saat Anda menggunakan aplikasi web kami dan layanan terkait (secara kolektif disebut "Layanan").</p>
<h2>2. Informasi yang Kami Kumpulkan</h2>
<p>Kami mengumpulkan beberapa jenis informasi dari dan tentang pengguna Layanan kami, termasuk:</p>
<ul>
<li><strong>Informasi Pribadi:</strong> Nama lengkap, alamat email, kata sandi, avatar, dan data profil lainnya.</li>
<li><strong>Data Operasional Tim:</strong> Log kehadiran (Daily Logs), data penagihan (Split Bills, Recurring Bills), daftar tugas (Tasks), dan transaksi buku kas.</li>
<li><strong>Data Teknis:</strong> Alamat IP, jenis peramban (browser), sistem operasi, dan log aktivitas sistem.</li>
<li><strong>Cookie dan Teknologi Pelacakan:</strong> Kami menggunakan cookie untuk mempertahankan sesi Anda dan meningkatkan pengalaman pengguna.</li>
</ul>
<h2>3. Bagaimana Kami Menggunakan Informasi Anda</h2>
<p>Kami menggunakan informasi yang kami kumpulkan dengan berbagai cara, termasuk untuk:</p>
<ul>
<li>Menyediakan, mengoperasikan, dan memelihara Layanan.</li>
<li>Mengelola akun Anda dan memberdayakan fitur kolaborasi tim.</li>
<li>Mengirimkan notifikasi administratif, pembaruan keamanan, dan undangan tim (melalui email).</li>
<li>Menganalisis tren pemakaian untuk memperbaiki dan mengoptimalkan UI/UX Layanan.</li>
<li>Memproses pembayaran Anda (jika ada langganan premium) melalui pihak ketiga yang aman.</li>
</ul>
<h2>4. Berbagi Data dengan Pihak Ketiga</h2>
<p>Kami tidak menjual, memperdagangkan, atau menyewakan informasi identitas pribadi Anda kepada pihak lain. Kami hanya dapat membagikan informasi dalam situasi berikut:</p>
<ul>
<li><strong>Keperluan Tim Anda:</strong> Data yang Anda masukkan ke sistem dapat dilihat oleh anggota tim lain (seperti Admin atau Lead) sesuai pengaturan hak akses (RBAC).</li>
<li><strong>Penyedia Layanan:</strong> Kami dapat mempekerjakan perusahaan pihak ketiga yang membutuhkan akses ke data Anda untuk melakukan tugas atas nama kami secara rahasia.</li>
<li><strong>Kepatuhan Hukum:</strong> Kami dapat mengungkapkan informasi Anda jika diwajibkan oleh hukum atau panggilan pengadilan.</li>
</ul>
<h2>5. Keamanan Data</h2>
<p>Kami telah menerapkan langkah-langkah teknis dan organisasi yang sesuai yang dirancang untuk mengamankan informasi pribadi Anda dari kehilangan yang tidak disengaja dan dari akses, penggunaan, perubahan, dan pengungkapan yang tidak sah. Server kami menggunakan koneksi aman (HTTPS/SSL), dan kredensial sensitif seperti kata sandi telah di-hash menggunakan algoritma Bcrypt yang kuat.</p>
<h2>6. Penyimpanan Data</h2>
<p>Kami hanya akan menyimpan informasi pribadi Anda selama diperlukan untuk memenuhi tujuan pengumpulannya, termasuk untuk memenuhi persyaratan hukum, akuntansi, atau pelaporan.</p>
<h2>7. Hak-Hak Anda</h2>
<p>Tergantung pada lokasi Anda, Anda mungkin memiliki hak berikut terkait data pribadi Anda:</p>
<ul>
<li>Hak untuk mengakses, memperbarui, atau menghapus informasi yang kami miliki tentang Anda.</li>
<li>Hak untuk memperbaiki (rectification).</li>
<li>Hak untuk membatasi atau menolak pemrosesan.</li>
<li>Hak atas portabilitas data.</li>
</ul>
<p>Untuk menggunakan hak-hak ini, Anda dapat menghubungi Lead tim Anda atau mengelola profil di dashboard, atau menghubungi kami secara langsung.</p>
<h2>8. Privasi Anak-anak</h2>
<p>Layanan kami tidak ditujukan untuk individu di bawah usia 16 tahun. Kami tidak secara sadar mengumpulkan informasi pribadi dari anak-anak. Jika kami menyadari bahwa kami telah mengumpulkan data pribadi dari seorang anak tanpa verifikasi persetujuan orang tua, kami akan mengambil langkah-langkah untuk menghapus informasi tersebut.</p>
<h2>9. Perubahan pada Kebijakan Privasi Ini</h2>
<p>Kami dapat memperbarui Kebijakan Privasi kami dari waktu ke waktu. Kami akan memberi tahu Anda tentang segala perubahan dengan memposting Kebijakan Privasi baru di halaman ini dan memperbarui "Tanggal Pembaruan".</p>
<h2>10. Hubungi Kami</h2>
<p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini atau praktik data kami, silakan hubungi kami di: <strong>info@teamvora.coded.my.id</strong>.</p>', 'group' => 'marketing', 'type' => 'string'],

            // ==================
            // SYARAT & KETENTUAN
            // ==================
            ['key' => 'terms_content', 'value' => '<h2>1. Penerimaan Syarat</h2>
<p>Dengan mengakses dan menggunakan platform TeamVora ("Layanan"), Anda menyatakan setuju untuk terikat oleh Syarat dan Ketentuan ("Syarat") ini. Jika Anda tidak menyetujui bagian mana pun dari Syarat ini, Anda tidak diperkenankan untuk menggunakan Layanan kami.</p>
<h2>2. Deskripsi Layanan</h2>
<p>TeamVora adalah platform manajemen tim dan operasional bisnis yang mencakup fitur pelacakan kehadiran (Daily Logs), manajemen kas (Cash Book), penagihan otomatis (Recurring Bills), dan pengelolaan tugas (Task Management). Kami berhak untuk mengubah, menangguhkan, atau menghentikan Layanan apa pun kapan saja dengan atau tanpa pemberitahuan sebelumnya.</p>
<h2>3. Pendaftaran Akun</h2>
<ul>
<li>Anda harus memberikan informasi yang akurat, lengkap, dan terkini saat mendaftar.</li>
<li>Anda bertanggung jawab untuk menjaga kerahasiaan kata sandi Anda dan membatasi akses ke komputer atau perangkat seluler Anda.</li>
<li>Satu akun tim (Organisasi) dikelola oleh pengguna dengan peran Lead atau Admin yang memiliki wewenang penuh atas data organisasi tersebut.</li>
</ul>
<h2>4. Kewajiban Pengguna</h2>
<p>Saat menggunakan Layanan kami, Anda setuju untuk TIDAK:</p>
<ul>
<li>Menggunakan Layanan untuk tujuan ilegal atau tidak sah.</li>
<li>Melanggar hukum di wilayah hukum Anda (termasuk, namun tidak terbatas pada, undang-undang hak cipta dan privasi).</li>
<li>Mengirimkan virus, worm, atau kode yang bersifat merusak.</li>
<li>Mencoba mendapatkan akses tidak sah ke sistem kami atau jaringan yang terhubung ke Layanan kami.</li>
</ul>
<h2>5. Kekayaan Intelektual</h2>
<p>Platform TeamVora, termasuk desain, logo, fitur, dan fungsionalitasnya, sepenuhnya merupakan milik TeamVora dan dilindungi oleh hak cipta internasional, merek dagang, paten, rahasia dagang, dan undang-undang kekayaan intelektual lainnya. Konten yang Anda unggah tetap menjadi milik Anda.</p>
<h2>6. Privasi dan Data Pengguna</h2>
<p>Penggunaan Anda atas Layanan juga tunduk pada Kebijakan Privasi kami. Dengan menggunakan Layanan, Anda menyetujui pengumpulan dan penggunaan informasi Anda oleh TeamVora. Tim Anda (Lead/Admin) juga memiliki akses ke data operasional yang Anda masukkan ke dalam sistem.</p>
<h2>7. Batasan Tanggung Jawab</h2>
<p>Dalam keadaan apa pun, TeamVora, direktur, karyawan, mitra, atau agennya tidak akan bertanggung jawab atas kerusakan tidak langsung, insidental, khusus, konsekuensial, atau hukuman, termasuk namun tidak terbatas pada hilangnya keuntungan, data, penggunaan, atau kerugian tak berwujud lainnya yang diakibatkan oleh:</p>
<ul>
<li>Akses atau penggunaan atau ketidakmampuan Anda untuk mengakses atau menggunakan Layanan.</li>
<li>Setiap tindakan atau konten dari pihak ketiga mana pun di Layanan.</li>
<li>Akses tidak sah, penggunaan, atau pengubahan transmisi atau konten Anda.</li>
</ul>
<h2>8. Biaya dan Pembayaran (Jika Berlaku)</h2>
<p>Beberapa fitur di TeamVora mungkin memerlukan pembayaran atau langganan berbayar. Dengan memilih paket berbayar, Anda menyetujui syarat harga dan pembayaran yang berlaku saat itu. Biaya tidak dapat dikembalikan kecuali diatur lain secara hukum.</p>
<h2>9. Modifikasi Syarat</h2>
<p>Kami berhak, atas kebijakan kami sendiri, untuk mengubah atau mengganti Syarat ini kapan saja. Jika revisi merupakan perubahan materi, kami akan mencoba memberikan pemberitahuan setidaknya 30 hari sebelum syarat baru berlaku.</p>
<h2>10. Hubungi Kami</h2>
<p>Jika Anda memiliki pertanyaan tentang Syarat ini, silakan hubungi kami di: <strong>info@teamvora.coded.my.id</strong>.</p>', 'group' => 'marketing', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
