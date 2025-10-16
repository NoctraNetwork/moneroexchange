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
        Schema::create('escrow_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->onDelete('cascade');
            $table->enum('direction', ['in', 'out', 'fee']);
            $table->bigInteger('amount_atomic');
            $table->string('tx_hash', 64)->nullable();
            $table->bigInteger('height')->nullable();
            $table->integer('confirmations')->default(0);
            $table->timestamps();

            $table->index(['trade_id', 'direction']);
            $table->index('tx_hash');
            $table->index(['height', 'confirmations']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escrow_movements');
    }
};

