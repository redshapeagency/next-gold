<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['purchase', 'sale']);
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->decimal('total_gross', 12, 2);
            $table->decimal('total_net', 12, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onUpdate('cascade');
            $table->foreignId('updated_by')->constrained('users')->onUpdate('cascade');
            $table->timestamps();

            $table->index(['type', 'date', 'status']);
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
