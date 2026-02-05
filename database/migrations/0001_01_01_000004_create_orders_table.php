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
			$table->unsignedBigInteger('cutomer');
			$table->decimal('paid_amount');
			$table->decimal('taxes')->default(0.15);
			$table->enum('status', ['PAID','UNPAID','CANCELLED']);
			$table->unsignedBigInteger('payment');
			$table->timestamps();
			$table->foreign('cutomer')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
			$table->foreign('payment')->references('id')->on('payments')->onDelete('restrict')->onUpdate('restrict');
			
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
