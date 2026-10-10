<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('isbn', 20)->nullable()->unique();
            $table->text('description')->nullable();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('publisher_id')
                ->constrained('publishers')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('publication_year');
            $table->unsignedInteger('stock')->default(0);

            $table->string('cover_image')->nullable();

            $table->enum('status', ['available', 'unavailable'])
                ->default('available');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
