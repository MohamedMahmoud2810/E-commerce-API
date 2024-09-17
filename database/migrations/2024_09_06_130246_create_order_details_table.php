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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');  // References the order
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // References the product
            $table->integer('quantity'); // Number of items
            $table->decimal('price', 8, 2); // Price of the product at the time of the order
            $table->decimal('total', 8, 2); // Total price for the order item (price * quantity)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
