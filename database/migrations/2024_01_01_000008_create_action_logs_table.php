<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->string('model');
            $table->unsignedBigInteger('model_id');
            $table->jsonb('diff')->nullable();
            $table->string('ip');
            $table->text('user_agent');
            $table->timestamp('created_at');

            $table->index(['model', 'model_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
