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
		Schema::create('orders', function (Blueprint $table) {
			$table->id('o_id');
			$table->string('order_number');
			$table->unsignedBigInteger('cutomer');
			$table->string('currency',3);
			$table->decimal('subtotal');
			$table->decimal('tax')->default(0);
			$table->decimal('shipping')->default(0);
			$table->decimal('discount')->default(0);
			$table->enum('status', ['PENDING','PAID','SHIPPING','CANCELLED']);
			$table->json('shipping_address')->nullable();
			$table->json('billing_address')->nullable();
			$table->json('user_agent')->nullable();
			$table->text('notes')->nullable();
			$table->timestamps();
			$table->foreign('cutomer')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
			
		});
	}
	
	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('orders');
	}
};
