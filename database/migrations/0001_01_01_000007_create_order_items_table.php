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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('item_id');
			$table->unsignedBigInteger('order_id');
			$table->unsignedBigInteger('product_id');
			$table->integer('quantity');
			$table->decimal('unit_price');
            $table->timestamps();
			$table->foreign('order_id')->references('o_id')->on('orders')->onDelete('restrict')->onUpdate('restrict');
			$table->foreign('product_id')->references('pro_id')->on('products')->onDelete('restrict')->onUpdate('restrict');
			
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
