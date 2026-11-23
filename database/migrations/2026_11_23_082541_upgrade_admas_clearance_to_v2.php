<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'batch')) {
                $table->string('batch')->nullable()->after('department');
            }
        });

        Schema::table('clearance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('clearance_requests', 'student_affairs_status')) {
                $table->string('student_affairs_status')->default('pending')->after('finance_status');
            }
            $table->string('department_remark')->nullable();
            $table->string('library_remark')->nullable();
            $table->string('finance_remark')->nullable();
            $table->string('student_affairs_remark')->nullable();
            $table->string('certificate_serial')->nullable()->unique();
            $table->string('certificate_status')->nullable(); // 'valid' or 'revoked'
            $table->timestamp('issued_at')->nullable();
        });

        Schema::create('clearance_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clearance_notifications');
    }
};