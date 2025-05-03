<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status_reason')) {
                $table->string('status_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'status_updated_at')) {
                $table->timestamp('status_updated_at')->nullable()->after('status_reason');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status_reason', 'status_updated_at']);
        });
    }
}; 