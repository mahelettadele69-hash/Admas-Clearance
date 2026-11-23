<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clearance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->text('remarks')->nullable();
            $table->string('library_status')->default('pending');
            $table->string('department_status')->default('pending');
            $table->string('finance_status')->default('pending');
            $table->string('registrar_status')->default('pending');
            $table->string('overall_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clearance_requests');
    }
};