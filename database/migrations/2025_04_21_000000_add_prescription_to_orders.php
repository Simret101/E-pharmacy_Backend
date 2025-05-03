<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('prescription_uid')->nullable();
            $table->string('prescription_image')->nullable();
            $table->foreign('prescription_uid')
                  ->references('prescription_uid')
                  ->on('prescriptions')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['prescription_uid']);
            $table->dropColumn(['prescription_uid', 'prescription_image']);
        });
    }
}; 