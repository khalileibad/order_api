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
        Schema::create('payments', function (Blueprint $table) {
            
			$table->id('pay_id');
			$table->unsignedBigInteger('order_id');
			$table->string('transaction_id')->unique();
			$table->string('gateway');
			$table->string('payment_method');
			$table->decimal('amount', 10, 2);
			$table->string('currency', 3)->default('EGP');
			$table->string('status')->default('pending');
			$table->string('gateway_transaction_id')->nullable();
			$table->string('gateway_reference')->nullable();
			$table->text('gateway_response')->nullable();
			$table->string('payment_url')->nullable();
			$table->unsignedBigInteger('cutomer');
			$table->json('metadata')->nullable();
			$table->text('description')->nullable();
			$table->timestamp('initiate_at')->nullable();
			$table->timestamp('paid_at')->nullable();
			$table->timestamp('expires_at')->nullable();
			$table->timestamps();
			
			$table->index(['status', 'created_at']);
			$table->index(['order_id', 'gateway']);
			$table->index(['transaction_id', 'gateway_transaction_id']);
			
			$table->foreign('cutomer')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
			$table->foreign('order_id')->references('o_id')->on('orders')->onDelete('restrict')->onUpdate('restrict');
			
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
