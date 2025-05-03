<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // First, drop the existing items column
            $table->dropColumn('items');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add the items column as text with a default empty array
            $table->text('items')->default('[]');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('items');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->text('items');
        });
    }
}; 