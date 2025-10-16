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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->string('pin_hash');
            $table->integer('pin_attempts')->default(0);
            $table->timestamp('pin_locked_until')->nullable();
            $table->text('pgp_public_key')->nullable();
            $table->string('pgp_fpr')->nullable();
            $table->timestamp('pgp_verified_at')->nullable();
            $table->string('country', 2)->nullable();
            $table->boolean('is_tor_only')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('is_tor_only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
