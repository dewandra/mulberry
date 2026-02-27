<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Feedback;
use App\Models\Project;
use App\Models\ProjectPic;
use App\Models\ProjectPreview;
use App\Models\ProjectStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('feedbacks')->delete();
        DB::table('project_previews')->delete();
        DB::table('project_status_history')->delete();
        DB::table('project_pics')->delete();
        DB::table('projects')->delete();
        DB::table('users')->whereNot('email', 'admin@act.com')->delete();
        DB::table('clients')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── 1. SUPER ADMIN (existing, ensure exists) ──────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@act.com'],
            [
                'id'        => Str::uuid(),
                'full_name' => 'Super Administrator',
                'password'  => Hash::make('password'),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );

        // ── 2. ADMIN (agency internal) ────────────────────────────────────────
        $admin = User::create([
            'id'        => Str::uuid(),
            'full_name' => 'Ahmad Rizki',
            'email'     => 'ahmad@act.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // ── 3. CLIENTS (companies) ────────────────────────────────────────────
        $clientData = [
            ['name' => 'PT Sinar Nusantara',  'slug' => 'sinar', 'email' => 'info@sinarnusantara.co.id', 'phone' => '021-5551234', 'address' => 'Jl. Sudirman No.1, Jakarta'],
            ['name' => 'CV Maju Bersama',     'slug' => 'maju',  'email' => 'info@majubersama.com',      'phone' => '022-5552345', 'address' => 'Jl. Asia Afrika No.5, Bandung'],
            ['name' => 'PT Kreatif Digital',  'slug' => 'kreatif','email' => 'info@kreatifdigital.id',   'phone' => '031-5553456', 'address' => 'Jl. Pemuda No.10, Surabaya'],
            ['name' => 'Toko Rasa Nusantara', 'slug' => 'trn',   'email' => 'info@rasanusantara.co',     'phone' => '0274-5554567','address' => 'Jl. Malioboro No.99, Yogyakarta'],
        ];

        $clients = collect($clientData)->map(fn($c) => Client::create([
            'id'           => Str::uuid(),
            'company_name' => $c['name'],
            'email'        => $c['email'],
            'phone'        => $c['phone'],
            'address'      => $c['address'],
            'is_active'    => true,
            'created_by'   => $superAdmin->id,
        ]))->values();

        [$clientSinar, $clientMaju, $clientKreatif, $clientTrn] = $clients;
        $slugs = ['sinar', 'maju', 'kreatif', 'trn'];

        // ── 4. CLIENT MANAGERS (role: client, dapat lihat semua project perusahaan)
        //       PIC perusahaan (role: pic, client_id = perusahaan mereka)
        //
        //  Struktur per perusahaan:
        //    - 1 manager (role: client) → melihat SEMUA project perusahaan
        //    - 2-3 PIC    (role: pic)   → hanya project yang di-assign ke mereka
        // ─────────────────────────────────────────────────────────────────────

        $managers = [];
        $pics     = [];

        $companyUsers = [
            // PT Sinar Nusantara
            0 => [
                'manager' => ['full_name' => 'Budi Hendrato',   'email' => 'budi@sinarnusantara.co.id'],
                'pics'    => [
                    ['full_name' => 'Sari Dewi',    'email' => 'sari@sinarnusantara.co.id'],
                    ['full_name' => 'Hendra Lim',   'email' => 'hendra@sinarnusantara.co.id'],
                ],
            ],
            // CV Maju Bersama
            1 => [
                'manager' => ['full_name' => 'Fitria Handayani', 'email' => 'fitri@majubersama.com'],
                'pics'    => [
                    ['full_name' => 'Agus Santoso',  'email' => 'agus@majubersama.com'],
                    ['full_name' => 'Rina Wulandari', 'email' => 'rina@majubersama.com'],
                ],
            ],
            // PT Kreatif Digital
            2 => [
                'manager' => ['full_name' => 'Gita Pratiwi',    'email' => 'gita@kreatifdigital.id'],
                'pics'    => [
                    ['full_name' => 'Doni Irawan',   'email' => 'doni@kreatifdigital.id'],
                    ['full_name' => 'Mega Putri',    'email' => 'mega@kreatifdigital.id'],
                    ['full_name' => 'Yuda Prasetya', 'email' => 'yuda@kreatifdigital.id'],
                ],
            ],
            // Toko Rasa Nusantara
            3 => [
                'manager' => ['full_name' => 'Joko Widodo',     'email' => 'joko@rasanusantara.co'],
                'pics'    => [
                    ['full_name' => 'Ani Kusuma',    'email' => 'ani@rasanusantara.co'],
                    ['full_name' => 'Bagas Nugroho', 'email' => 'bagas@rasanusantara.co'],
                ],
            ],
        ];

        foreach ($clients as $i => $client) {
            $cu = $companyUsers[$i];

            // Create manager
            $managers[$i] = User::create([
                'id'        => Str::uuid(),
                'full_name' => $cu['manager']['full_name'],
                'email'     => $cu['manager']['email'],
                'password'  => Hash::make('password'),
                'role'      => 'client',
                'client_id' => $client->id,
                'is_active' => true,
            ]);

            // Create PICs — all belong to same client
            $pics[$i] = collect($cu['pics'])->map(fn($p) => User::create([
                'id'        => Str::uuid(),
                'full_name' => $p['full_name'],
                'email'     => $p['email'],
                'password'  => Hash::make('password'),
                'role'      => 'pic',
                'client_id' => $client->id,  // ← SAMA dengan client mereka
                'is_active' => true,
            ]))->values();
        }

        // ── 5. PROJECTS ───────────────────────────────────────────────────────
        // Setiap project pakai PIC dari perusahaan yang sama!
        // $pics[0] = PICs Sinar, $pics[1] = PICs Maju, dst.

        $projects = [

            // ── PT Sinar Nusantara ──────────────────────────────────────────
            $this->makeProject('Brosur Produk Ramadan 2025',   $clientSinar->id, 'brief',                     'normal', $admin->id,
                'Desain brosur A4 untuk promosi produk Ramadan.',
                now()->addDays(30), [$pics[0][0]->id]),

            $this->makeProject('Katalog Musim Panas 2025',     $clientSinar->id, 'feedback_received',         'normal', $admin->id,
                'Katalog produk 16 halaman untuk koleksi musim panas.',
                now()->addDays(10), [$pics[0][0]->id, $pics[0][1]->id]),

            $this->makeProject('Undangan Gala Dinner',         $clientSinar->id, 'fa_sent',                   'high',   $admin->id,
                'Desain undangan eksklusif untuk gala dinner tahunan.',
                now()->subDays(5), [$pics[0][1]->id]),

            // ── CV Maju Bersama ─────────────────────────────────────────────
            $this->makeProject('Banner Anniversary 10 Tahun',  $clientMaju->id,  'scheduled',                 'high',   $admin->id,
                'Banner standing dan spanduk untuk acara anniversary.',
                now()->addDays(14), [$pics[1][0]->id]),

            $this->makeProject('Social Media Kit Q2 2025',     $clientMaju->id,  'preview_sent',              'high',   $admin->id,
                'Template post Instagram, Facebook, dan TikTok untuk Q2.',
                now()->addDays(5), [$pics[1][1]->id]),

            $this->makeProject('Buku Tahunan 2024',            $clientMaju->id,  'project_closed',            'normal', $admin->id,
                'Buku tahunan perusahaan 2024 sebanyak 60 halaman.',
                now()->subDays(30), [$pics[1][0]->id, $pics[1][1]->id]),

            // ── PT Kreatif Digital ──────────────────────────────────────────
            $this->makeProject('Desain Logo Rebranding',       $clientKreatif->id,'work_in_progress',         'high',   $admin->id,
                'Rebranding total logo perusahaan termasuk brand guideline.',
                now()->addDays(20), [$pics[2][0]->id]),

            $this->makeProject('Kartu Nama Direksi',           $clientKreatif->id,'artwork_approved',         'low',    $admin->id,
                'Desain kartu nama untuk 5 orang direktur.',
                now()->subDays(2), [$pics[2][1]->id]),

            $this->makeProject('Campaign Lebaran Nasional',    $clientKreatif->id,'work_in_progress',         'high',   $admin->id,
                'Materi campaign Lebaran: poster, digital ads, dan billboard.',
                now()->addDays(8), [$pics[2][0]->id, $pics[2][1]->id, $pics[2][2]->id]),

            // ── Toko Rasa Nusantara (TRN) ───────────────────────────────────
            //   joko@rasanusantara.co (manager) → lihat SEMUA 3 project TRN
            //   ani@rasanusantara.co  (PIC)     → hanya project 1 & 3
            //   bagas@rasanusantara.co (PIC)    → hanya project 2

            $this->makeProject('Packaging Produk Snack',       $clientTrn->id,   'preview_sent',              'normal', $admin->id,
                'Desain kemasan produk snack 3 varian rasa.',
                now()->addDays(7), [$pics[3][0]->id]),            // ani

            $this->makeProject('X-Banner Pameran Industry',    $clientTrn->id,   'final_artwork_preparation', 'normal', $admin->id,
                'X-banner 60x160cm untuk stand pameran industri makanan.',
                now()->subDays(1), [$pics[3][1]->id]),            // bagas

            $this->makeProject('Merchandise Ulang Tahun',      $clientTrn->id,   'brief',                     'low',    $admin->id,
                'Desain merchandise (tote bag, mug, pin) untuk HUT ke-5.',
                now()->addDays(45), [$pics[3][0]->id]),           // ani
        ];

        // ── 6. PREVIEWS & FEEDBACKS ───────────────────────────────────────────

        // Katalog Sinar — feedback_received
        $p = $projects[1];
        $prev1 = $this->makePreview($p, 'v1', 'Draft Pertama Katalog',
            "Preview pertama katalog musim panas 2025.\nHalaman 1-2: Cover&intro, 3-8: Koleksi pakaian, 9-14: Aksesoris, 15-16: Back cover.",
            $admin->id, now()->subDays(3), "Internal: Layout sudah oke.");
        $this->makeFeedback($p, $prev1, $managers[0],
            "Bagus! Tapi halaman 5-6 background terlalu gelap untuk foto produk. Bisa diganti lebih terang?",
            now()->subDays(2));
        $this->makeFeedback($p, $prev1, $managers[0],
            "Font judul juga kurang besar, tolong diperbesar sekitar 20%.",
            now()->subDays(2)->addHours(1));
        $this->makeFeedback($p, $prev1, $pics[0][0],
            "Siap Bu, akan kami revisi dan kirim v2 dalam 2 hari kerja!",
            now()->subDays(2)->addHours(2));

        // Undangan Gala Dinner Sinar — fa_sent (v1 + feedback + v2 + approve)
        $p = $projects[2];
        $prev1 = $this->makePreview($p, 'v1', 'Desain Undangan Eksklusif',
            "Preview undangan gala dinner premium. Menggunakan konsep gold foil.",
            $admin->id, now()->subDays(12));
        $this->makeFeedback($p, $prev1, $managers[0],
            "Elegan sekali! Request: tambahkan emboss pada logo di cover depan?",
            now()->subDays(10));
        $prev2 = $this->makePreview($p, 'v2', 'Final: Emboss Effect Added',
            "Emboss logo sudah ditambahkan. File siap produksi.",
            $admin->id, now()->subDays(8));
        $this->makeFeedback($p, $prev2, $managers[0],
            "Luar biasa! Persis yang kami bayangkan. Approved! 🎉",
            now()->subDays(7));

        // Social Media Kit Maju — preview_sent v2
        $p = $projects[4];
        $prev1 = $this->makePreview($p, 'v1', 'Template Set Awal',
            "Preview pertama Social Media Kit Q2 2025:\n- 5 template feed Instagram\n- 3 template story\n- 2 template Facebook",
            $admin->id, now()->subDays(8));
        $this->makeFeedback($p, $prev1, $managers[1],
            "Menarik! Tapi warna kurang bold dan tolong tambahkan template LinkedIn juga.",
            now()->subDays(6));
        $prev2 = $this->makePreview($p, 'v2', 'Revisi: Warna Bold + LinkedIn',
            "v2: Warna dipertebal sesuai brand guide baru. Ditambahkan 3 template LinkedIn.",
            $admin->id, now()->subHours(12));

        // Buku Tahunan Maju — project_closed (full cycle)
        $p = $projects[5];
        $prev1 = $this->makePreview($p, 'v1', 'Draft Buku Tahunan 2024',
            "Draft pertama buku tahunan 2024, 60 halaman.",
            $admin->id, now()->subDays(45));
        $this->makeFeedback($p, $prev1, $managers[1],
            "Banyak yang perlu direvisi. Layout halaman 10-20 perlu dirapikan, foto halaman 35 blur.",
            now()->subDays(42));
        $prev2 = $this->makePreview($p, 'v2', 'Revisi Final Buku Tahunan',
            "Semua revisi sudah diaplikasikan.",
            $admin->id, now()->subDays(38));
        $this->makeFeedback($p, $prev2, $managers[1],
            "Sudah bagus! Approved untuk cetak. ✅",
            now()->subDays(36));

        // Kartu Nama Kreatif — artwork_approved (v1 + feedback + v2 + approve)
        $p = $projects[7];
        $prev1 = $this->makePreview($p, 'v1', 'Desain Kartu Nama Direktur',
            "Preview kartu nama untuk 5 direktur. Kertas matte 350gsm dengan UV spot.",
            $admin->id, now()->subDays(10));
        $this->makeFeedback($p, $prev1, $managers[2],
            "Sudah sesuai! Koreksi kecil: jabatan Pak Budi dari 'Director Operations' jadi 'Chief Operating Officer'.",
            now()->subDays(8));
        $prev2 = $this->makePreview($p, 'v2', 'Final: Koreksi Jabatan',
            "Jabatan Pak Budi sudah diupdate.",
            $admin->id, now()->subDays(6));
        $this->makeFeedback($p, $prev2, $managers[2],
            "Perfect! Approve untuk cetak! 🎉",
            now()->subDays(5));

        // Packaging TRN — preview_sent v1 (no feedback yet)
        $p = $projects[9];
        $prev1 = $this->makePreview($p, 'v1', 'Preview Awal - 3 Varian',
            "Preview pertama kemasan untuk 3 varian rasa:\n- Original\n- Balado\n- Keju\n\nMohon review dan berikan feedback.",
            $admin->id, now()->subHours(5),
            "Internal: Warna sudah sesuai brief.");

        // X-Banner TRN — fa_prep (v1 + feedback + v2 + approve)
        $p = $projects[10];
        $prev1 = $this->makePreview($p, 'v1', 'Konsep X-Banner Pameran',
            "Preview X-Banner 60x160cm. Tema: Industri Makanan Modern.",
            $admin->id, now()->subDays(5));
        $this->makeFeedback($p, $prev1, $managers[3],
            "Keren! Logo perusahaan di pojok kanan bawah terlalu kecil. Bisa diperbesar 150%?",
            now()->subDays(4));
        $prev2 = $this->makePreview($p, 'v2', 'Final: Logo Diperbesar',
            "Logo sudah diperbesar 150% dan diposisikan lebih prominenst.",
            $admin->id, now()->subDays(3));
        $this->makeFeedback($p, $prev2, $managers[3],
            "Approved! Terima kasih, bisa diproses untuk final artwork. 👍",
            now()->subDays(2));

        $this->command->info('');
        $this->command->info('✅ DemoSeeder selesai! Data demo berhasil dibuat.');
        $this->command->info('');
        $this->command->info('── Struktur Perusahaan ──────────────────────────────────────────');
        $this->command->table(
            ['Perusahaan', 'Role', 'Email', 'Password'],
            [
                ['─', 'Super Admin', 'admin@act.com', 'password'],
                ['─', 'Admin',       'ahmad@act.com', 'password'],
                ['PT Sinar Nusantara', 'Manager (Client)', 'budi@sinarnusantara.co.id',  'password'],
                ['PT Sinar Nusantara', 'PIC',              'sari@sinarnusantara.co.id',   'password'],
                ['PT Sinar Nusantara', 'PIC',              'hendra@sinarnusantara.co.id', 'password'],
                ['CV Maju Bersama',    'Manager (Client)', 'fitri@majubersama.com',       'password'],
                ['CV Maju Bersama',    'PIC',              'agus@majubersama.com',        'password'],
                ['CV Maju Bersama',    'PIC',              'rina@majubersama.com',        'password'],
                ['PT Kreatif Digital', 'Manager (Client)', 'gita@kreatifdigital.id',      'password'],
                ['PT Kreatif Digital', 'PIC',              'doni@kreatifdigital.id',      'password'],
                ['PT Kreatif Digital', 'PIC',              'mega@kreatifdigital.id',      'password'],
                ['PT Kreatif Digital', 'PIC',              'yuda@kreatifdigital.id',      'password'],
                ['Toko Rasa Nusantara','Manager (Client)', 'joko@rasanusantara.co',       'password'],
                ['Toko Rasa Nusantara','PIC',              'ani@rasanusantara.co',        'password'],
                ['Toko Rasa Nusantara','PIC',              'bagas@rasanusantara.co',      'password'],
            ]
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeProject(
        string $name, string $clientId, string $status, string $priority,
        string $createdBy, string $description, $deadline, array $picIds = []
    ): Project {
        $project = Project::create([
            'project_name' => $name,
            'client_id'    => $clientId,
            'status'       => $status,
            'priority'     => $priority,
            'description'  => $description,
            'deadline'     => $deadline,
            'is_active'    => true,
            'created_by'   => $createdBy,
        ]);

        foreach ($picIds as $picId) {
            DB::table('project_pics')->insert([
                'id'          => Str::uuid(),
                'project_id'  => $project->id,
                'pic_user_id' => $picId,
                'assigned_at' => now(),
                'assigned_by' => $createdBy,
            ]);
        }

        ProjectStatusHistory::create([
            'project_id'  => $project->id,
            'from_status' => null,
            'to_status'   => 'brief',
            'notes'       => 'Project created',
            'changed_by'  => $createdBy,
            'changed_at'  => $project->created_at,
        ]);

        return $project;
    }

    private function makePreview(
        Project $project, string $version, string $title, string $description,
        string $sentBy, $sentAt = null, ?string $internalNotes = null
    ): ProjectPreview {
        return ProjectPreview::create([
            'project_id'     => $project->id,
            'version'        => $version,
            'title'          => $title,
            'description'    => $description,
            'internal_notes' => $internalNotes,
            'sent_by'        => $sentBy,
            'sent_at'        => $sentAt ?? now(),
            'is_active'      => true,
        ]);
    }

    private function makeFeedback(
        Project $project, ProjectPreview $preview, User $submittedBy,
        string $comment, $submittedAt = null
    ): Feedback {
        return Feedback::create([
            'project_id'   => $project->id,
            'preview_id'   => $preview->id,
            'comment'      => $comment,
            'submitted_by' => $submittedBy->id,
            'submitted_at' => $submittedAt ?? now(),
            'is_active'    => true,
        ]);
    }
}
