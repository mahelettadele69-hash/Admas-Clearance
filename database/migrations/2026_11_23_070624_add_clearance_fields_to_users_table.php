<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'student_id')) {
                $table->string('student_id')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student')->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'department', 'role']);
        });
    }
};