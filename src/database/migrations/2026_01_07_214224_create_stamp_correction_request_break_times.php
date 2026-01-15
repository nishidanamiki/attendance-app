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
        Schema::create('stamp_correction_request_break_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stamp_correction_request_id');
            $table->foreignId('break_time_id')->nullable()->constrained('break_times')->nullOnDelete();
            $table->time('break_in_at')->nullable();
            $table->time('break_out_at')->nullable();
            $table->timestamps();
            $table->index(['stamp_correction_request_id'], 'scrbt_id_idx');
            $table->foreign('stamp_correction_request_id', 'scrbt_scr_id_fk')->references('id')->on('stamp_correction_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamp_correction_request_break_times');
    }
};
