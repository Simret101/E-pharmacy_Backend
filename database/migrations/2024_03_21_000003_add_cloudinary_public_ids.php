<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'license_public_id')) {
                $table->string('license_public_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'tin_public_id')) {
                $table->string('tin_public_id')->nullable();
            }
        });

        if (Schema::hasTable('drugs')) {
            Schema::table('drugs', function (Blueprint $table) {
                if (!Schema::hasColumn('drugs', 'public_id')) {
                    $table->string('public_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('prescriptions', 'public_id')) {
                    $table->string('public_id')->nullable();
                }
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['license_public_id', 'tin_public_id']);
        });

        if (Schema::hasTable('drugs')) {
            Schema::table('drugs', function (Blueprint $table) {
                $table->dropColumn('public_id');
            });
        }

        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropColumn('public_id');
            });
        }
    }
}; 