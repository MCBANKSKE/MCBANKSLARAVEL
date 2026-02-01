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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // google, github, twitter, etc.
            $table->string('provider_id')->unique(); // Unique ID from provider
            $table->string('provider_token')->nullable(); // OAuth token
            $table->string('provider_refresh_token')->nullable(); // Refresh token
            $table->integer('provider_expires_in')->nullable(); // Token expiration
            $table->json('provider_data')->nullable(); // Additional provider data
            $table->string('nickname')->nullable(); // Provider username
            $table->string('name')->nullable(); // Full name from provider
            $table->string('email')->nullable(); // Email from provider
            $table->string('avatar')->nullable(); // Avatar URL from provider
            $table->timestamps();

            // Indexes for performance
            $table->unique(['user_id', 'provider']);
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
