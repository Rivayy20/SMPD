<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->string('membership_number', 50)->unique();
            $table->string('name');
            $table->string('identity_number', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();

            $table->date('join_date');

            $table->foreignId('membership_type_id')
                ->constrained('membership_types')
                ->restrictOnDelete();

            $table->string('card_number', 50)->nullable()->unique();
            $table->string('photo')->nullable();

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
