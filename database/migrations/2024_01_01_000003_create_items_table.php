<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('material', ['gold', 'silver', 'platinum', 'other']);
            $table->integer('karat')->nullable(); // per oro
            $table->decimal('purity', 5, 3)->nullable(); // per altri metalli
            $table->decimal('weight_grams', 8, 3);
            $table->decimal('price_purchase', 12, 2);
            $table->decimal('price_sale', 12, 2);
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['in_stock', 'archived'])->default('in_stock');
            $table->foreignId('created_by')->constrained('users')->onUpdate('cascade');
            $table->foreignId('updated_by')->constrained('users')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id', 'name']);
            $table->index('material');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
