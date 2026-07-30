<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Enums\ProjectStatus;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Roles
        $rolePemohon = Role::firstOrCreate(['name' => 'pemohon', 'guard_name' => 'web']);
        $rolePenilai = Role::firstOrCreate(['name' => 'penilai', 'guard_name' => 'web']);

        $passwordHash = Hash::make('password');
        $now = now();

        // 2. Generate 1.000 User Pemohon (Bulk Insert)
        $this->command->info('Generating 1.000 pemohon users...');
        $pemohonData = [];
        for ($i = 1; $i <= 1000; $i++) {
            $pemohonData[] = [
                'name' => "Pemohon {$i}",
                'email' => "pemohon{$i}@persetujuan.id",
                'email_verified_at' => $now,
                'password' => $passwordHash,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        $pemohonChunks = array_chunk($pemohonData, 200);
        foreach ($pemohonChunks as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Ambil ID User Pemohon yang baru dimasukkan
        $pemohonIds = User::orderBy('id', 'asc')->limit(1000)->pluck('id')->toArray();

        // Assign Role Pemohon
        $modelHasRolesPemohon = [];
        foreach ($pemohonIds as $id) {
            $modelHasRolesPemohon[] = [
                'role_id' => $rolePemohon->id,
                'model_type' => User::class,
                'model_id' => $id,
            ];
        }
        DB::table('model_has_roles')->insert($modelHasRolesPemohon);

        // 3. Generate 1.000 User Penilai (Bulk Insert)
        $this->command->info('Generating 1.000 penilai users...');
        $penilaiData = [];
        for ($i = 1; $i <= 1000; $i++) {
            $penilaiData[] = [
                'name' => "Penilai {$i}",
                'email' => "penilai{$i}@persetujuan.id",
                'email_verified_at' => $now,
                'password' => $passwordHash,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $penilaiChunks = array_chunk($penilaiData, 200);
        foreach ($penilaiChunks as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Ambil ID User Penilai
        $penilaiIds = User::whereNotIn('id', $pemohonIds)->orderBy('id', 'asc')->pluck('id')->toArray();

        // Assign Role Penilai
        $modelHasRolesPenilai = [];
        foreach ($penilaiIds as $id) {
            $modelHasRolesPenilai[] = [
                'role_id' => $rolePenilai->id,
                'model_type' => User::class,
                'model_id' => $id,
            ];
        }
        DB::table('model_has_roles')->insert($modelHasRolesPenilai);

        // 4. Generate 10.000 Projects (Bulk Insert)
        $this->command->info('Generating 10.000 projects...');
        $projectStatuses = array_column(ProjectStatus::cases(), 'value');
        $projectData = [];

        for ($i = 1; $i <= 10000; $i++) {
            $randomPemohonId = $pemohonIds[array_rand($pemohonIds)];
            $randomStatus = $projectStatuses[array_rand($projectStatuses)];
            
            $projectData[] = [
                'project_code' => 'PRJ-' . strtoupper(Str::random(8)) . '-' . $i,
                'user_id' => $randomPemohonId,
                'title' => "Project Pengajuan Kelayakan Ke-" . $i,
                'description' => "Deskripsi detail untuk dokumen kelayakan permohonan ke-{$i} dengan status {$randomStatus}.",
                'status' => $randomStatus,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $projectChunks = array_chunk($projectData, 500);
        foreach ($projectChunks as $chunk) {
            DB::table('projects')->insert($chunk);
        }

        // 5. Generate Initial Logs & Documents (Bulk Insert)
        $this->command->info('Generating sample project logs and documents...');
        $projectIds = DB::table('projects')->pluck('id')->toArray();
        
        $logData = [];
        $docData = [];

        foreach ($projectIds as $projId) {
            // Minimal ada log draft awal untuk setiap project
            $logData[] = [
                'project_id' => $projId,
                'actor_id' => $pemohonIds[array_rand($pemohonIds)],
                'old_status' => null,
                'new_status' => 'DRAFT',
                'remarks' => 'Sistem menginisiasi pengajuan baru.',
                'created_at' => $now,
            ];

            // Tambahkan sample document ke 50% project secara acak
            if (rand(0, 1) === 1) {
                $docData[] = [
                    'project_id' => $projId,
                    'file_name' => "dokumen_kelayakan_" . Str::random(5) . ".pdf",
                    'file_path' => "projects/{$projId}/documents/" . Str::random(20) . ".pdf",
                    'file_size' => rand(500000, 4500000), // ~500KB - 4.5MB
                    'mime_type' => 'application/pdf',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $logChunks = array_chunk($logData, 500);
        foreach ($logChunks as $chunk) {
            DB::table('application_logs')->insert($chunk);
        }

        if (!empty($docData)) {
            $docChunks = array_chunk($docData, 500);
            foreach ($docChunks as $chunk) {
                DB::table('project_documents')->insert($chunk);
            }
        }

        $this->command->info('Database seeding completed successfully.');
    }
}
