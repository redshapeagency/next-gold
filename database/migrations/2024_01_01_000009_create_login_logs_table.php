<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ip');
            $table->text('user_agent');
            $table->boolean('success');
            $table->string('attempted_email')->nullable();
            $table->string('attempted_username')->nullable();
            $table->timestamp('created_at');

            $table->index('created_at');
            $table->index(['ip', 'success']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
