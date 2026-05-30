<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->string('id')->primary(); // PJR-001, etc.
            $table->string('judul');
            $table->string('kategori');
            $table->text('deskripsi');
            $table->string('pelapor');
            $table->string('status')->default('pending');
            $table->boolean('is_anonim')->default(false);
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
