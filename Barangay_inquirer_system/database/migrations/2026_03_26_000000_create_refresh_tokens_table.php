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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Plain token (returned to user, encrypted in transit over HTTPS)
            $table->string('token', 255)
                ->unique()
                ->comment('Plain token - never returned after creation');

            // Hashed token for secure storage and comparison
            $table->string('token_hash', 255)
                ->unique()
                ->index()
                ->comment('SHA-256 hash of token for verification');

            // Request context (for audit and security)
            $table->ipAddress('ip_address')
                ->nullable()
                ->comment('IP address that issued this token');

            $table->string('user_agent', 255)
                ->nullable()
                ->comment('User agent that issued this token');

            // Token lifecycle
            $table->dateTime('expires_at')
                ->index()
                ->comment('When this refresh token expires');

            $table->dateTime('rotated_at')
                ->nullable()
                ->comment('When this token was rotated/replaced');

            // Token status
            $table->boolean('is_revoked')
                ->default(false)
                ->index()
                ->comment('Whether this token has been revoked');

            // Timestamps
            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'is_revoked']);
            $table->index(['expires_at', 'is_revoked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
