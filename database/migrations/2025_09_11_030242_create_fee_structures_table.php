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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('school_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('name');
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->string('frequency');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
