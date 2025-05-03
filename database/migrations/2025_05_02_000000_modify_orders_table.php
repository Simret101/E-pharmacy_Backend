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
            // Add the items column as JSON without default value
            $table->json('items');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('items');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('items');
        });
    }
}; 