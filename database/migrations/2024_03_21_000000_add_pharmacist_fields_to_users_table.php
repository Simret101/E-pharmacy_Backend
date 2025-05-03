<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'license_image')) {
                $table->string('license_image')->nullable();
            }
            if (!Schema::hasColumn('users', 'tin_image')) {
                $table->string('tin_image')->nullable();
            }
            if (!Schema::hasColumn('users', 'tin_number')) {
                $table->string('tin_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'account_number')) {
                $table->string('account_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'pharmacy_name')) {
                $table->string('pharmacy_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('pending');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'license_image',
                'tin_image',
                'tin_number',
                'account_number',
                'bank_name',
                'pharmacy_name',
                'lat',
                'lng',
                'status'
            ]);
        });
    }
}; 