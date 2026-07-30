<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('certificate_path')->nullable()->after('status');
            $table->timestamp('certificate_generated_at')->nullable()->after('certificate_path');

            $table->index('certificate_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['certificate_generated_at']);
            $table->dropColumn(['certificate_path', 'certificate_generated_at']);
        });
    }
};
