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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('material', ['gold', 'argento', 'platino', 'altro']);
            $table->integer('karat')->nullable();
            $table->decimal('purity', 5, 2)->nullable();
            $table->decimal('weight_grams', 8, 3);
            $table->decimal('price_purchase', 12, 2);
            $table->decimal('price_sale', 12, 2);
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['in_stock', 'archived'])->default('in_stock');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'category_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
