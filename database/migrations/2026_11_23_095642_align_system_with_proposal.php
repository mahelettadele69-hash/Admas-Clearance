<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clearance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('clearance_requests', 'store_status')) {
                $table->string('store_status')->default('pending')->after('library_remark');
                $table->string('store_remark')->nullable()->after('store_status');
            }
            if (!Schema::hasColumn('clearance_requests', 'registrar_remark')) {
                $table->string('registrar_remark')->nullable()->after('registrar_status');
            }
            if (!Schema::hasColumn('clearance_requests', 'verification_code')) {
                $table->string('verification_code', 16)->nullable()->after('certificate_serial');
            }
        });

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::table('clearance_requests', function (Blueprint $table) {
            $table->dropColumn(['store_status', 'store_remark', 'registrar_remark', 'verification_code']);
        });
    }
};