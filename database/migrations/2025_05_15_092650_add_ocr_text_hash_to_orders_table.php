<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'ocr_text_hash')) {
                $table->string('ocr_text_hash')->nullable()->after('ocr_text');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'ocr_text_hash')) {
                $table->dropColumn('ocr_text_hash');
            }
        });
    }
};
