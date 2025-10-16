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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('side', ['buy', 'sell']);
            $table->enum('price_mode', ['fixed', 'floating']);
            $table->decimal('fixed_price', 20, 8)->nullable(); // base price for sellers
            $table->integer('margin_bps')->nullable(); // basis points for floating
            $table->integer('markup_percentage')->default(0); // seller markup in 1% increments (0-100)
            $table->string('currency', 3);
            $table->bigInteger('min_xmr_atomic');
            $table->bigInteger('max_xmr_atomic');
            $table->foreignId('payment_method_id')->constrained();
            $table->string('country', 2)->nullable();
            $table->enum('online_or_inperson', ['online', 'inperson']);
            $table->text('terms_md')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['side', 'active', 'created_at']);
            $table->index(['payment_method_id', 'active']);
            $table->index(['country', 'active']);
            $table->index(['currency', 'active']);
            $table->index(['online_or_inperson', 'active']);
            $table->index(['min_xmr_atomic', 'max_xmr_atomic']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
