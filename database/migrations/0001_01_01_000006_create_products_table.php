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
        Schema::create('products', function (Blueprint $table) {
			$table->id('pro_id');
			$table->string('pro_name');
			$table->unsignedBigInteger('category_id')->nullable();
			$table->text('pro_description')->nullable();
			$table->decimal('pro_price', 10, 2);
			$table->integer('pro_stock')->default(0);
			$table->string('pro_sku')->unique()->nullable();
			$table->string('barcode')->unique()->nullable();
			$table->string('image')->nullable();
			$table->json('attributes')->nullable();
			$table->boolean('is_active')->default(true);
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('category_id')->references('cat_id')->on('categories')->onDelete('restrict')->onUpdate('restrict');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
