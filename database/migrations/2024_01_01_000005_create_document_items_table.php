<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('material');
            $table->integer('karat')->nullable();
            $table->decimal('purity', 5, 3)->nullable();
            $table->decimal('weight_grams', 8, 3);
            $table->decimal('price_unit', 12, 2);
            $table->integer('qty')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_items');
    }
};
