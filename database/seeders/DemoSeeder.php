<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Feedback;
use App\Models\Project;
use App\Models\ProjectPreview;
use App\Models\ProjectStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    // ─── Thumbnail images dari Unsplash (bebas, no auth) ───────────────────────
    private array $thumbUrls = [
        'rebrand'      => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80',
        'digital_ads'  => 'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?w=800&q=80',
        'packaging'    => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&q=80',
        'annual_rpt'   => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80',
        'social_media' => 'https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=800&q=80',
        'catalog'      => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=800&q=80',
        'event_banner' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80',
        'food_pkg'     => 'https://images.unsplash.com/photo-1621939514649-280e2ee25f60?w=800&q=80',
        'merchandise'  => 'https://images.unsplash.com/photo-1503602642458-232111445657?w=800&q=80',
        'print_ad'     => 'https://images.unsplash.com/photo-1524234107056-1c1f48f64ab8?w=800&q=80',
    ];

    // ─── Logo perusahaan (foto gedung/kantor korporat, terlihat profesional) ─────
    private array $logoUrls = [
        'sinar'   => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&q=85', // gedung kaca modern Jakarta
        'maju'    => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&q=85', // interior kantor modern
        'kreatif' => 'https://images.unsplash.com/photo-1497366754035-f200581399c4?w=400&q=85', // kantor kreatif coworking
        'trn'     => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&q=85', // toko/food retail modern
    ];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('feedbacks')->truncate();
        DB::table('project_previews')->truncate();
        DB::table('project_status_history')->truncate();
        DB::table('project_pics')->truncate();
        DB::table('projects')->truncate();
        DB::table('users')->truncate();
        DB::table('clients')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Download thumbnails project ───────────────────────────────────────
        Storage::disk('public')->makeDirectory('thumbnails');
        $this->command->info('⬇️  Mendownload thumbnail project...');
        $thumbs = [];
        foreach ($this->thumbUrls as $key => $url) {
            $thumbs[$key] = $this->downloadThumb($url, 'thumb_' . $key);
        }
        $this->command->info('✅ Semua thumbnail siap.');

        // ── Download logo perusahaan ──────────────────────────────────────────
        Storage::disk('public')->makeDirectory('logos');
        $this->command->info('⬇️  Mendownload logo perusahaan...');
        $logos = [];
        foreach ($this->logoUrls as $key => $url) {
            $logos[$key] = $this->downloadLogo($url, $key);
        }
        $this->command->info('✅ Semua logo siap.');

        // ══════════════════════════════════════════════════════════════════════
        //  1. INTERNAL USERS (Super Admin & Admin)
        // ══════════════════════════════════════════════════════════════════════
        $superAdmin = User::create([
            'id'        => Str::uuid(),
            'full_name' => 'Super Administrator',
            'email'     => 'admin@act.com',
            'password'  => Hash::make('password'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);

        $admin = User::create([
            'id'        => Str::uuid(),
            'full_name' => 'Ahmad Rizki',
            'email'     => 'ahmad@act.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $creator = $superAdmin;

        // ══════════════════════════════════════════════════════════════════════
        //  2. CLIENT COMPANIES
        // ══════════════════════════════════════════════════════════════════════
        $companyData = [
            [
                'company_name'   => 'PT Sinar Nusantara',
                'email'          => 'info@sinarnusantara.co.id',
                'phone'          => '021-5551234',
                'address'        => 'Jl. Sudirman No. 1, Jakarta Pusat',
                'contact_person' => 'Budi Hendrato',
                'logo_key'       => 'sinar',
                'manager'        => ['full_name' => 'Budi Hendrato',     'email' => 'budi@sinarnusantara.co.id'],
                'pics'           => [
                    ['full_name' => 'Sari Dewi',    'email' => 'sari@sinarnusantara.co.id'],
                    ['full_name' => 'Hendra Lim',   'email' => 'hendra@sinarnusantara.co.id'],
                ],
            ],
            [
                'company_name'   => 'CV Maju Bersama',
                'email'          => 'info@majubersama.com',
                'phone'          => '022-5552345',
                'address'        => 'Jl. Asia Afrika No. 5, Bandung',
                'contact_person' => 'Fitria Handayani',
                'logo_key'       => 'maju',
                'manager'        => ['full_name' => 'Fitria Handayani',  'email' => 'fitri@majubersama.com'],
                'pics'           => [
                    ['full_name' => 'Agus Santoso',  'email' => 'agus@majubersama.com'],
                    ['full_name' => 'Rina Wulandari', 'email' => 'rina@majubersama.com'],
                ],
            ],
            [
                'company_name'   => 'PT Kreatif Digital',
                'email'          => 'info@kreatifdigital.id',
                'phone'          => '031-5553456',
                'address'        => 'Jl. Pemuda No. 10, Surabaya',
                'contact_person' => 'Gita Pratiwi',
                'logo_key'       => 'kreatif',
                'manager'        => ['full_name' => 'Gita Pratiwi',      'email' => 'gita@kreatifdigital.id'],
                'pics'           => [
                    ['full_name' => 'Doni Irawan',   'email' => 'doni@kreatifdigital.id'],
                    ['full_name' => 'Mega Putri',    'email' => 'mega@kreatifdigital.id'],
                    ['full_name' => 'Yuda Prasetya', 'email' => 'yuda@kreatifdigital.id'],
                ],
            ],
            [
                'company_name'   => 'Toko Rasa Nusantara',
                'email'          => 'info@rasanusantara.co',
                'phone'          => '0274-5554567',
                'address'        => 'Jl. Malioboro No. 99, Yogyakarta',
                'contact_person' => 'Joko Widodo',
                'logo_key'       => 'trn',
                'manager'        => ['full_name' => 'Joko Widodo',       'email' => 'joko@rasanusantara.co'],
                'pics'           => [
                    ['full_name' => 'Ani Kusuma',    'email' => 'ani@rasanusantara.co'],
                    ['full_name' => 'Bagas Nugroho', 'email' => 'bagas@rasanusantara.co'],
                ],
            ],
        ];

        $clients  = [];
        $managers = [];
        $pics     = [];

        foreach ($companyData as $i => $cd) {
            $logoKey  = $cd['logo_key'];
            $logoPath = $logos[$logoKey] ?? null;

            // Create company
            $client = Client::create([
                'id'              => Str::uuid(),
                'company_name'    => $cd['company_name'],
                'contact_person'  => $cd['contact_person'],
                'email'           => $cd['email'],
                'phone'           => $cd['phone'],
                'address'         => $cd['address'],
                'logo_url'        => $logoPath,
                'logo_filename'   => $logoPath ? basename($logoPath) : null,
                'is_active'       => true,
                'created_by'      => $creator->id,
            ]);
            $clients[$i] = $client;

            // Create manager (role: client)
            $managers[$i] = User::create([
                'id'        => Str::uuid(),
                'full_name' => $cd['manager']['full_name'],
                'email'     => $cd['manager']['email'],
                'password'  => Hash::make('password'),
                'role'      => 'client',
                'client_id' => $client->id,
                'is_active' => true,
            ]);

            // Create PICs (role: pic)
            $pics[$i] = collect($cd['pics'])->map(fn($p) => User::create([
                'id'        => Str::uuid(),
                'full_name' => $p['full_name'],
                'email'     => $p['email'],
                'password'  => Hash::make('password'),
                'role'      => 'pic',
                'client_id' => $client->id,
                'is_active' => true,
            ]))->values();
        }

        // ══════════════════════════════════════════════════════════════════════
        //  3. PROJECTS (10 project realistis, semua deadline ≤ hari ini)
        // ══════════════════════════════════════════════════════════════════════
        $this->command->info('📁 Membuat project...');

        $projectDefs = [
            // 1 ── PT Sinar Nusantara ──────────────────────────────────────────
            [
                'name'        => 'Rebranding Visual Identity 2026',
                'client_idx'  => 0,
                'status'      => 'brief',
                'priority'    => 'high',
                'description' => 'Proyek rebranding menyeluruh mencakup logo, color palette, typography, brand voice, dan brand guideline document 30+ halaman untuk seluruh divisi perusahaan.',
                'deadline'    => now()->subDays(3),
                'thumb'       => $thumbs['rebrand'],
                'pic_indices' => [0],      // Sari Dewi
                'created_at'  => now()->subDays(8),
            ],
            [
                'name'        => 'Laporan Tahunan 2025 (Annual Report)',
                'client_idx'  => 0,
                'status'      => 'preview_sent',
                'priority'    => 'high',
                'description' => 'Annual report 2025 sebanyak 48 halaman: infografis data keuangan, foto kegiatan CSR, pencapaian perusahaan, dan outlook 2026.',
                'deadline'    => now()->subDays(10),
                'thumb'       => $thumbs['annual_rpt'],
                'pic_indices' => [0, 1],   // Sari & Hendra
                'created_at'  => now()->subDays(35),
            ],
            [
                'name'        => 'Merchandise Karyawan HUT ke-15',
                'client_idx'  => 0,
                'status'      => 'fa_sent',
                'priority'    => 'low',
                'description' => 'Desain merchandise untuk perayaan HUT perusahaan ke-15: kaos, tote bag, mug, lanyard, pin enamel, dan packaging hadiahnya.',
                'deadline'    => now()->subDays(30),
                'thumb'       => $thumbs['merchandise'],
                'pic_indices' => [1],      // Hendra Lim
                'created_at'  => now()->subDays(60),
            ],

            // 2 ── CV Maju Bersama ─────────────────────────────────────────────
            [
                'name'        => 'Kampanye Digital Ramadan 2026',
                'client_idx'  => 1,
                'status'      => 'scheduled',
                'priority'    => 'high',
                'description' => 'Kampanye digital terintegrasi untuk Ramadan 2026: banner website, IG feed & story set (30 template), Facebook ads, TikTok creative, dan email newsletter template.',
                'deadline'    => now()->subDays(7),
                'thumb'       => $thumbs['digital_ads'],
                'pic_indices' => [0],      // Agus
                'created_at'  => now()->subDays(18),
            ],
            [
                'name'        => 'Katalog Produk B2B 2026',
                'client_idx'  => 1,
                'status'      => 'feedback_received',
                'priority'    => 'normal',
                'description' => 'Katalog produk B2B 24 halaman untuk distribusi ke reseller, distributor, dan partner bisnis di seluruh Indonesia.',
                'deadline'    => now()->subDays(20),
                'thumb'       => $thumbs['catalog'],
                'pic_indices' => [0, 1],   // Agus & Rina
                'created_at'  => now()->subDays(40),
            ],
            [
                'name'        => 'Print Ad Majalah Marketing Q4 2025',
                'client_idx'  => 1,
                'status'      => 'project_closed',
                'priority'    => 'low',
                'description' => 'Iklan cetak full page (A4) untuk majalah Marketing Indonesia dan MIX edisi Oktober-Desember 2025.',
                'deadline'    => now()->subDays(45),
                'thumb'       => $thumbs['print_ad'],
                'pic_indices' => [1],      // Rina
                'created_at'  => now()->subDays(75),
            ],

            // 3 ── PT Kreatif Digital ──────────────────────────────────────────
            [
                'name'        => 'Desain Kemasan Premium Gift Box',
                'client_idx'  => 2,
                'status'      => 'work_in_progress',
                'priority'    => 'normal',
                'description' => 'Gift box edisi terbatas untuk program loyalitas pelanggan: box utama, inner wrap, tissue paper berlogo, kartu ucapan, dan ribbon seal.',
                'deadline'    => now()->subDays(14),
                'thumb'       => $thumbs['packaging'],
                'pic_indices' => [0],      // Doni
                'created_at'  => now()->subDays(25),
            ],
            [
                'name'        => 'Booth Display Pameran IIMS 2026',
                'client_idx'  => 2,
                'status'      => 'artwork_approved',
                'priority'    => 'high',
                'description' => 'Desain booth 3×4m untuk pameran IIMS 2026: backdrop utama, 2 standing banner, floor sticker, header panel, dan merchandising display layout.',
                'deadline'    => now()->subDays(25),
                'thumb'       => $thumbs['event_banner'],
                'pic_indices' => [0, 1, 2],// Doni, Mega, Yuda
                'created_at'  => now()->subDays(45),
            ],

            // 4 ── Toko Rasa Nusantara ─────────────────────────────────────────
            [
                'name'        => 'Social Media Content Pack Q1 2026',
                'client_idx'  => 3,
                'status'      => 'preview_sent',
                'priority'    => 'normal',
                'description' => '40 template konten media sosial siap posting untuk Q1 2026: 20 IG feed, 10 IG story, 10 Facebook post. Termasuk preset caption dan hashtag guide.',
                'deadline'    => now()->subDays(1),
                'thumb'       => $thumbs['social_media'],
                'pic_indices' => [0],      // Ani
                'created_at'  => now()->subDays(20),
            ],
            [
                'name'        => 'Kemasan Produk Makanan Ringan (3 SKU)',
                'client_idx'  => 3,
                'status'      => 'final_artwork_preparation',
                'priority'    => 'normal',
                'description' => 'Desain kemasan untuk 3 SKU produk makanan ringan: Original, Pedas Manis, dan Keju Gurih. Termasuk informasi nutrisi dan barcode siap cetak.',
                'deadline'    => now()->subDays(5),
                'thumb'       => $thumbs['food_pkg'],
                'pic_indices' => [0, 1],   // Ani & Bagas
                'created_at'  => now()->subDays(30),
            ],
        ];

        $projects = [];
        foreach ($projectDefs as $idx => $def) {
            $client  = $clients[$def['client_idx']];
            $picIds  = collect($def['pic_indices'])->map(fn($pi) => $pics[$def['client_idx']][$pi]->id)->toArray();

            $p = $this->makeProject(
                name:       $def['name'],
                clientId:   $client->id,
                status:     $def['status'],
                priority:   $def['priority'],
                desc:       $def['description'],
                deadline:   $def['deadline'],
                thumbPath:  $def['thumb'],
                picIds:     $picIds,
                createdBy:  $creator->id,
                createdAt:  $def['created_at'],
            );

            $projects[$idx] = [
                'p'         => $p,
                'manager'   => $managers[$def['client_idx']],
                'client'    => $client,
            ];

            $this->command->line("  ✅ [{$def['status']}] {$def['name']}");
        }

        // ══════════════════════════════════════════════════════════════════════
        //  4. PREVIEW & FEEDBACK per project (sesuai status)
        // ══════════════════════════════════════════════════════════════════════
        $this->command->info('💬 Membuat preview & feedback realistis...');

        // ── [0] Sinar – Rebranding – BRIEF (belum ada preview)
        // (tidak ada preview untuk brief)

        // ── [1] Sinar – Annual Report – PREVIEW SENT (v1 sent, menunggu feedback)
        $this->makePreview($projects[1]['p'], 'v1',
            'Draft Laporan Tahunan 2025 – Mohon Review',
            "Selamat sore Pak Budi,\n\nPreview pertama Annual Report 2025 sudah kami kirimkan. Berikut ringkasan konten yang sudah kami siapkan:\n\n📄 Halaman 1–4   : Cover, daftar isi, pesan direksi\n📊 Halaman 5–18  : Infografis pencapaian & data keuangan\n📸 Halaman 19–32 : Dokumentasi kegiatan operasional & CSR\n🎯 Halaman 33–44 : Timeline milestone & proyeksi 2026\n📋 Halaman 45–48 : Penutup & informasi kontak\n\nMohon berikan feedback dalam 3 hari kerja.\n\nSalam,\nTim ACT Creative",
            $creator->id, now()->subDays(2),
            'QC internal sudah selesai. File 300dpi, CMYK siap offset.');

        // ── [2] Sinar – Merchandise – FA SENT (full cycle)
        $prev2_1 = $this->makePreview($projects[2]['p'], 'v1',
            'Konsep Awal Merchandise HUT ke-15',
            "Preview pertama desain merchandise. Konsep menggunakan warna gold & navy yang mewakili 15 tahun keberhasilan perusahaan.\n\nItem dalam satu set:\n👕 Kaos polo (S/M/L/XL)\n👜 Tote bag canvas\n☕ Mug keramik 300ml\n🏷️ Lanyard + card holder\n📌 Pin enamel (2 motif)\n📦 Premium packaging box",
            $creator->id, now()->subDays(52));
        $this->makeFeedback($projects[2]['p'], $prev2_1, $projects[2]['manager'],
            "Konsep gold & navy bagus! Catatan:\n1. Font judul di kaos terlalu tipis, pakai yang lebih bold\n2. Ukuran logo di mug terlalu kecil, perbesar 30%\n3. Warna tote bag ganti dari krem ke putih gading",
            now()->subDays(50));

        $prev2_2 = $this->makePreview($projects[2]['p'], 'v2',
            'Revisi Final Merchandise – Siap Approval',
            "Semua revisi sudah diaplikasikan:\n✅ Font kaos diperbesar dan diganti ke bold weight\n✅ Logo mug diperbesar 30%\n✅ Tote bag: krem → putih gading\n✅ Detail jahitan dan finishing diperlihatkan dalam mockup 3D",
            $creator->id, now()->subDays(45));
        $this->makeFeedback($projects[2]['p'], $prev2_2, $projects[2]['manager'],
            "Sempurna! Sudah persis yang kami inginkan. APPROVED untuk produksi! 🎉\nMohon segera diproses ke vendor, kami butuh sebelum tanggal anniversary.",
            now()->subDays(43));

        $this->makePreview($projects[2]['p'], 'FA',
            'FINAL ARTWORK – Dikirim ke Vendor Produksi',
            "Final Artwork telah dikirim ke PT Garuda Promosi (vendor merchandise rekanan).\n\nSpesifikasi teknis:\n📐 Kaos  : AI, CMYK, garis jahit terpisah\n📐 Mug   : PDF 300dpi, size sesuai template cetak\n📐 Tote  : CDR, pantone color guide disertakan\n📐 Pin   : AI vector, 2 file terpisah per motif\n\nETA produksi: 10–14 hari kerja.\nEstimasi selesai: sebelum tanggal anniversary.",
            $creator->id, now()->subDays(38),
            'File FA sudah di-backup di Google Drive proyek.');

        // ── [3] Maju – Kampanye Ramadan – SCHEDULED (baru dijadwalkan, belum WIP)
        // (tidak ada preview untuk scheduled)

        // ── [4] Maju – Katalog B2B – FEEDBACK RECEIVED
        $prev4_1 = $this->makePreview($projects[4]['p'], 'v1',
            'Draft Pertama Katalog Produk B2B 2026',
            "Halo Bu Fitria,\n\nDraft pertama Katalog B2B 2026 sudah kami selesaikan!\n\n📋 Struktur katalog:\n• Halaman 1–2   : Cover & welcome message\n• Halaman 3–6   : Profil perusahaan & keunggulan\n• Halaman 7–18  : Katalog produk per kategori\n• Halaman 19–22 : Tabel harga & minimum order\n• Halaman 23–24 : Cara order & kontak distributor\n\nMohon review dan feedback.",
            $creator->id, now()->subDays(15));
        $this->makeFeedback($projects[4]['p'], $prev4_1, $projects[4]['manager'],
            "layout-nya bagus tapi ada beberapa yang perlu diperbaiki:\n\n1. **Halaman 7**: Gambar produk kategori elektronik terlihat blur, mohon ganti dengan file resolusi lebih tinggi\n2. **Halaman 15**: Info harga belum terupdate (masih pakai harga 2024)\n3. **Halaman 19**: Mohon tambahkan kolom 'diskon untuk reorder' di tabel harga\n4. **Logo partner**: Logo distributor di halaman terakhir ada 2 yang tertutup elemen lain",
            now()->subDays(12));
        $this->makeFeedback($projects[4]['p'], $prev4_1, $projects[4]['manager'],
            "Oh iya, tambahan: mohon font tabel harga diperbesar sedikit ya, agak susah dibaca di hasil cetak.",
            now()->subDays(12)->addHours(3));

        // ── [5] Maju – Print Ad – PROJECT CLOSED (full cycle selesai)
        $prev5_1 = $this->makePreview($projects[5]['p'], 'v1',
            'Konsep Iklan Majalah – Draft Awal',
            "Preview pertama iklan cetak untuk majalah Marketing Indonesia edisi Q4 2025.\n\nKonsep: Bold typography dengan visual produk hero di center, tagline di bawah.",
            $creator->id, now()->subDays(68));
        $this->makeFeedback($projects[5]['p'], $prev5_1, $projects[5]['manager'],
            "Konsepnya menarik! Perlu revisi:\n- Background terlalu ramai, sederhanakan\n- Ukuran logo terlalu kecil di pojok kanan\n- Tagline font kurang impactful",
            now()->subDays(65));

        $prev5_2 = $this->makePreview($projects[5]['p'], 'v2',
            'Revisi – Background Disederhanakan',
            "Revisi sesuai feedback:\n✅ Background disederhanakan jadi putih bersih\n✅ Logo diperbesar\n✅ Font tagline diganti ke bold condensed",
            $creator->id, now()->subDays(60));
        $this->makeFeedback($projects[5]['p'], $prev5_2, $projects[5]['manager'],
            "Jauh lebih baik! APPROVED untuk pengiriman ke redaksi majalah. ✅",
            now()->subDays(58));

        $this->makePreview($projects[5]['p'], 'FA',
            'Final Artwork Dikirim ke Redaksi',
            "Final artwork sudah dikirim ke redaksi Marketing Indonesia dan MIX.\n\nFile: PDF/X-1a, CMYK, 300dpi, bleed 5mm. Konfirmasi penerimaan dari redaksi sudah masuk.",
            $creator->id, now()->subDays(52));

        // ── [6] Kreatif – Kemasan Gift Box – WORK IN PROGRESS (belum ada preview)
        // (tidak ada preview untuk WIP)

        // ── [7] Kreatif – Booth IIMS – ARTWORK APPROVED (v1 + revisi + approve)
        $prev7_1 = $this->makePreview($projects[7]['p'], 'v1',
            'Konsep Booth IIMS 2026 – Review Diperlukan',
            "Selamat siang Bu Gita,\n\nKonsep awal desain booth IIMS 2026 sudah kami selesaikan!\n\n🎨 Konsep: 'Tech-Meets-Nature' – perpaduan elemen digital & organik\n\n📐 Detail elemen:\n• Backdrop utama 3×2.4m : Visual hero produk + brand statement\n• Standing banner kiri   : Spesifikasi & keunggulan produk\n• Standing banner kanan  : QR code untuk katalog digital\n• Floor sticker 3×4m    : Pattern branding\n• Header panel           : Logo + nama perusahaan\n\nMohon review dan feedback.",
            $creator->id, now()->subDays(22));
        $this->makeFeedback($projects[7]['p'], $prev7_1, $projects[7]['manager'],
            "Konsepnya bagus! Beberapa revisi:\n1. Warna dominan ganti dari biru ke hijau tosca (sesuai identitas visual terbaru kami)\n2. Foto produk di backdrop pakai yang sudah kami kirim via email (foto baru, lebih hi-res)\n3. QR code di standing banner kanan linkkan ke microsite Ramadan, bukan website utama\n4. Ukuran font nama perusahaan di header perlu lebih besar 20%",
            now()->subDays(19));

        $prev7_2 = $this->makePreview($projects[7]['p'], 'v2',
            'Revisi Final Booth – Siap Approval',
            "Revisi sudah diaplikasikan:\n✅ Warna dominan: biru → hijau tosca\n✅ Foto produk backdrop: sudah pakai file baru dari klien\n✅ QR code: sudah update ke microsite Ramadan\n✅ Font header: diperbesar 20%\n✅ Touchup spacing dan typography minor",
            $creator->id, now()->subDays(15));
        $this->makeFeedback($projects[7]['p'], $prev7_2, $projects[7]['manager'],
            "Luar biasa! Persis yang kami bayangkan. APPROVED untuk produksi! 🎉\nTolong segera proses ya, deadline booth sudah dekat.",
            now()->subDays(13));

        $projects[7]['p']->update(['artwork_approved_at' => now()->subDays(13)]);

        // ── [8] TRN – Social Media Pack – PREVIEW SENT (v1 dikirim, tunggu feedback)
        $this->makePreview($projects[8]['p'], 'v1',
            'Social Media Content Pack Q1 2026 – Preview Awal',
            "Halo Pak Joko,\n\nSocial Media Content Pack Q1 2026 sudah siap untuk direview!\n\n📱 Isi pack:\n• 20 template IG Feed (4 tema warna berbeda)\n• 10 template IG Story (countdown, promo, quotes)\n• 10 template Facebook Post\n\n🎨 Konsep visual: Warm tones + ilustrasi makanan tradisional\n📝 Bonus: Panduan caption + 200 hashtag rekomendasiI\n\nSilakan review dan berikan feedback dalam 3 hari kerja ya, Pak.",
            $creator->id, now()->subDays(2),
            'Sudah dicek konsistensi font dan warna brand TRN.');

        // ── [9] TRN – Kemasan Makanan – FINAL ARTWORK PREP
        $prev9_1 = $this->makePreview($projects[9]['p'], 'v1',
            'Preview Kemasan 3 SKU – Draft Pertama',
            "Halo Pak Joko,\n\nPreview pertama kemasan untuk 3 SKU produk makanan ringan!\n\n🍟 Original  : Konsep kuning-oranye (ceria & fresh)\n🌶️ Pedas Manis: Konsep merah-gradasi (bold & intense)\n🧀 Keju Gurih : Konsep kuning-hijau (playful & appetizing)\n\nSemua sudah memuat:\n✅ Nama produk & tagline\n✅ Berat bersih & info nutrisi (format BPOM)\n✅ Kode barcode placeholder\n✅ Sertifikasi Halal logo",
            $creator->id, now()->subDays(18));
        $this->makeFeedback($projects[9]['p'], $prev9_1, $projects[9]['manager'],
            "Suka banget konsepnya! Revisi minor:\n1. SKU Pedas Manis: tambahkan ikon cabai yang lebih besar di sudut kanan atas\n2. Semua SKU: naikkan ukuran teks 'Halal' sedikit agar lebih visible\n3. Barcode posisinya dipindah ke bawah, bukan samping info nutrisi",
            now()->subDays(14));

        $prev9_2 = $this->makePreview($projects[9]['p'], 'v2',
            'Revisi Kemasan – Siap Approval',
            "Semua revisi diaplikasikan:\n✅ Pedas Manis: ikon cabai diperbesar & direposisi ke kanan atas\n✅ Semua SKU: ukuran teks Halal dinaikkan\n✅ Barcode dipindah ke bagian bawah\n✅ Minor adjustment: margin dan spacing keseluruhan",
            $creator->id, now()->subDays(8));
        $this->makeFeedback($projects[9]['p'], $prev9_2, $projects[9]['manager'],
            "Keren banget hasilnya! APPROVED semua 3 SKU! ✅\nLanjutkan ke final artwork ya, deadline sudah dekat.",
            now()->subDays(6));

        $projects[9]['p']->update(['artwork_approved_at' => now()->subDays(6)]);

        $this->makePreview($projects[9]['p'], 'FA',
            'FINAL ARTWORK – Siap Cetak',
            "Final Artwork untuk 3 SKU sudah siap ke percetakan!\n\nSpesifikasi:\n📐 Format  : PDF/X-4, CMYK\n📐 Resolusi: 350dpi (lebih dari standar minimum)\n📐 Bleed   : 3mm semua sisi\n📐 Pantone : PMS 7404C (kuning), PMS 485C (merah), PMS 376C (hijau)\n\n⚠️ Catatan khusus untuk percetakan:\n- Laminasi glossy untuk Original & Keju\n- Laminasi soft touch untuk Pedas Manis",
            $creator->id, now()->subDays(3),
            'File sudah dikirim ke PT Gramedia Printing via WeTransfer. Konfirmasi penerimaan sudah masuk.');

        // ══════════════════════════════════════════════════════════════════════
        //  5. SUMMARY OUTPUT
        // ══════════════════════════════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('🎉 DemoSeeder selesai! Semua data berhasil dibuat.');
        $this->command->info('');
        $this->command->info('── Login Credentials ─────────────────────────────────────────────────');
        $this->command->table(
            ['Role', 'Perusahaan', 'Email', 'Password'],
            [
                ['Super Admin',  '–',                    'admin@act.com',                 'password'],
                ['Admin',        '–',                    'ahmad@act.com',                 'password'],
                ['─────────────', '──────────────────────', '──────────────────────────────', '────────'],
                ['Client (Mgr)', 'PT Sinar Nusantara',  'budi@sinarnusantara.co.id',     'password'],
                ['PIC',          'PT Sinar Nusantara',  'sari@sinarnusantara.co.id',     'password'],
                ['PIC',          'PT Sinar Nusantara',  'hendra@sinarnusantara.co.id',   'password'],
                ['─────────────', '──────────────────────', '──────────────────────────────', '────────'],
                ['Client (Mgr)', 'CV Maju Bersama',     'fitri@majubersama.com',         'password'],
                ['PIC',          'CV Maju Bersama',     'agus@majubersama.com',          'password'],
                ['PIC',          'CV Maju Bersama',     'rina@majubersama.com',          'password'],
                ['─────────────', '──────────────────────', '──────────────────────────────', '────────'],
                ['Client (Mgr)', 'PT Kreatif Digital',  'gita@kreatifdigital.id',        'password'],
                ['PIC',          'PT Kreatif Digital',  'doni@kreatifdigital.id',        'password'],
                ['PIC',          'PT Kreatif Digital',  'mega@kreatifdigital.id',        'password'],
                ['PIC',          'PT Kreatif Digital',  'yuda@kreatifdigital.id',        'password'],
                ['─────────────', '──────────────────────', '──────────────────────────────', '────────'],
                ['Client (Mgr)', 'Toko Rasa Nusantara', 'joko@rasanusantara.co',         'password'],
                ['PIC',          'Toko Rasa Nusantara', 'ani@rasanusantara.co',          'password'],
                ['PIC',          'Toko Rasa Nusantara', 'bagas@rasanusantara.co',        'password'],
            ]
        );
    }


    private function makeProject(
        string $name, string $clientId, string $status, string $priority,
        string $desc, $deadline, ?string $thumbPath, array $picIds,
        string $createdBy, $createdAt
    ): Project {
        $project = Project::create([
            'project_name'  => $name,
            'client_id'     => $clientId,
            'status'        => $status,
            'priority'      => $priority,
            'description'   => $desc,
            'deadline'      => $deadline,
            'thumbnail_url' => $thumbPath,
            'is_active'     => true,
            'created_by'    => $createdBy,
        ]);

        // Backdate timestamps
        DB::table('projects')->where('id', $project->id)->update([
            'created_at' => $createdAt,
            'updated_at' => $createdAt->copy()->addDays(rand(1, 4)),
        ]);

        foreach ($picIds as $picId) {
            DB::table('project_pics')->insert([
                'id'          => Str::uuid(),
                'project_id'  => $project->id,
                'pic_user_id' => $picId,
                'assigned_at' => $createdAt,
                'assigned_by' => $createdBy,
            ]);
        }

        return $project;
    }

    private function makePreview(
        Project $p, string $version, string $title, string $desc,
        string $sentBy, $sentAt = null, ?string $notes = null
    ): ProjectPreview {
        return ProjectPreview::create([
            'project_id'     => $p->id,
            'version'        => $version,
            'title'          => $title,
            'description'    => $desc,
            'internal_notes' => $notes,
            'sent_by'        => $sentBy,
            'sent_at'        => $sentAt ?? now(),
            'is_active'      => true,
        ]);
    }

    private function makeFeedback(
        Project $p, ProjectPreview $prev, User $by,
        string $comment, $at = null
    ): Feedback {
        return Feedback::create([
            'project_id'   => $p->id,
            'preview_id'   => $prev->id,
            'comment'      => $comment,
            'submitted_by' => $by->id,
            'submitted_at' => $at ?? now(),
            'is_active'    => true,
        ]);
    }

    private function downloadThumb(string $url, string $filename): ?string
    {
        try {
            $path = "thumbnails/{$filename}.jpg";
            if (Storage::disk('public')->exists($path)) {
                $this->command->line("  ⏩ Skip: {$path}");
                return $path;
            }
            $res = Http::timeout(30)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            if ($res->successful()) {
                Storage::disk('public')->put($path, $res->body());
                $this->command->line("  ⬇️  Downloaded: {$path}");
                return $path;
            }
        } catch (\Exception $e) {
            $this->command->warn("  ⚠️  Gagal download {$filename}: " . $e->getMessage());
        }
        return null;
    }

    private function downloadLogo(string $url, string $companyKey): ?string
    {
        try {
            $path = "logos/logo_{$companyKey}.jpg";
            if (Storage::disk('public')->exists($path)) {
                $this->command->line("  ⏩ Skip: {$path}");
                return $path;
            }
            $res = Http::timeout(30)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            if ($res->successful()) {
                Storage::disk('public')->put($path, $res->body());
                $this->command->line("  ⬇️  Downloaded logo: {$path}");
                return $path;
            }
        } catch (\Exception $e) {
            $this->command->warn("  ⚠️  Gagal download logo {$companyKey}: " . $e->getMessage());
        }
        return null;
    }
}
