<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')->constrained('quality_checks')->cascadeOnDelete();
            $table->string('parameter');
            $table->string('expected_value')->nullable();
            $table->string('actual_value')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_check_items');
    }
};
