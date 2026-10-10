<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_facilities', function (Blueprint $table) {
            $table->foreignId('area_id')
                ->constrained('reading_areas')
                ->cascadeOnDelete();

            $table->foreignId('facility_id')
                ->constrained('facilities')
                ->cascadeOnDelete();

            $table->primary(['area_id', 'facility_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_facilities');
    }
};
