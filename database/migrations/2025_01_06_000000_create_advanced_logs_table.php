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
        Schema::create('advanced_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index(); // emergency, alert, critical, error, warning, notice, info, debug
            $table->string('category', 50)->nullable()->index(); // auth, api, payments, etc.
            $table->text('message');
            $table->json('context')->nullable(); // Additional data as JSON
            $table->string('user_id')->nullable()->index(); // User who triggered the log
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->string('user_agent')->nullable();
            $table->string('request_id')->nullable()->index(); // For request correlation
            $table->string('session_id')->nullable()->index();
            $table->string('route_name')->nullable(); // Named route
            $table->string('method', 10)->nullable(); // HTTP method
            $table->string('url')->nullable(); // Request URL
            $table->integer('status_code')->nullable(); // HTTP status code
            $table->decimal('execution_time', 8, 3)->nullable(); // Execution time in seconds
            $table->bigInteger('memory_usage')->nullable(); // Memory usage in bytes
            $table->string('file')->nullable(); // Source file
            $table->integer('line')->nullable(); // Source line
            $table->string('exception_class')->nullable(); // Exception class name
            $table->text('exception_message')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->json('tags')->nullable(); // Custom tags for filtering
            $table->json('extra')->nullable(); // Additional metadata
            $table->boolean('is_resolved')->default(false)->index(); // For issue tracking
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['level', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['level', 'category', 'created_at']);
            $table->index(['is_resolved', 'created_at']);

            // Full text search on message
            $table->fullText(['message']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advanced_logs');
    }
};
