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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['trade', 'withdrawal', 'deposit']);
            $table->integer('bps'); // basis points
            $table->bigInteger('flat_atomic')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['type', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};

