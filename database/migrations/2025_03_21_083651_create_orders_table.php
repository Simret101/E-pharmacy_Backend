<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to users table
            $table->foreignId('drug_id')->constrained()->onDelete('cascade'); // Link to drugs table
            $table->string('prescription_uid')->unique(); // Unique identifier for the prescription
            $table->string('prescription_image')->nullable(); // Path to the prescription image
            $table->boolean('refill_allowed')->default(false); // Whether refills are allowed
            $table->integer('refill_used')->default(0); // Number of refills used
            $table->string('prescription_status')->default('pending'); // Status of the prescription
            $table->integer('quantity'); // Quantity of the drug ordered
            $table->decimal('total_amount', 10, 2); // Total amount for the order
            $table->string('status')->default('pending'); // Order status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}