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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name'); // Name of the drug
            $table->string('brand')->nullable(); // Brand name (optional)
            $table->text('description')->nullable(); // Drug description
            $table->string('category');
            $table->decimal('price', 10, 2); // Price with two decimal places
            $table->integer('stock')->default(0); // Available stock
            $table->string('dosage')->nullable(); // Dosage information
            $table->string('image')->nullable(); // Drug image
            $table->timestamp('expires_at')->nullable(); // Expiration date of the drug
            $table->unsignedBigInteger('created_by'); // User who created the drug
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps(); // created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
