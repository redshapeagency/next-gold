<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('tax_code')->unique();
            $table->string('id_doc_type'); // carta_identita, patente, passaporto
            $table->string('id_doc_number');
            $table->string('id_doc_issuer');
            $table->date('id_doc_issue_date');
            $table->string('address');
            $table->string('city');
            $table->string('zip');
            $table->string('province');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onUpdate('cascade');
            $table->foreignId('updated_by')->constrained('users')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['last_name', 'first_name']);
            $table->index('tax_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
