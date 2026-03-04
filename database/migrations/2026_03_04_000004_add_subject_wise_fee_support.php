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
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->boolean('is_subject_wise')->default(false)->after('frequency');
        });

        Schema::create('fee_structure_subjects', function (Blueprint $table) {
            $table->foreignUlid('fee_structure_id')->constrained('fee_structures')->cascadeOnDelete();
            $table->foreignUlid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['fee_structure_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structure_subjects');

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn('is_subject_wise');
        });
    }
};
