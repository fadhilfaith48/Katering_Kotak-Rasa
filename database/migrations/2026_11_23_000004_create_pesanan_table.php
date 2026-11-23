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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
            $table->enum('jalur_pesanan', ['langsung', 'whatsapp'])->default('whatsapp');
            $table->date('tanggal_pesan');
            $table->date('tanggal_acara');
            $table->enum('status', ['baru', 'diproses', 'selesai', 'batal'])->default('baru');
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status_bayar', ['belum_bayar', 'dp', 'lunas'])->default('belum_bayar');
            $table->text('catatan_khusus')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
