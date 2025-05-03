<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_uid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('attachment_path');
            $table->unsignedInteger('refill_allowed')->default(1);
            $table->unsignedInteger('refill_used')->default(0);
            $table->enum('status', ['pending', 'partially_filled', 'fulfilled'])->default('pending');
            $table->timestamps();
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('prescription');
    }
};
