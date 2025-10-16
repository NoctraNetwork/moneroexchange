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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->foreignId('offer_id')->constrained();
            $table->enum('state', [
                'draft',
                'await_deposit',
                'escrowed',
                'await_payment',
                'release_pending',
                'refunded',
                'completed',
                'disputed',
                'cancelled'
            ])->default('draft');
            $table->bigInteger('amount_atomic');
            $table->decimal('price_per_xmr', 20, 8);
            $table->string('currency', 3);
            $table->string('escrow_subaddr', 95); // Monero subaddress
            $table->string('buyer_address', 95)->nullable(); // Monero address
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_id', 'state']);
            $table->index(['seller_id', 'state']);
            $table->index(['state', 'created_at']);
            $table->index('escrow_subaddr');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};

