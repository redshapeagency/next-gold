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
        Schema::create('gold_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->decimal('bid', 10, 2);
            $table->decimal('ask', 10, 2);
            $table->enum('unit', ['g', 'oz']);
            $table->string('currency', 3);
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_quotes');
    }
};
