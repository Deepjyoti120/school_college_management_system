<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_subjects') || !Schema::hasColumn('user_subjects', 'id')) {
            return;
        }

        $primary = DB::selectOne("
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_schema = current_schema()
              AND table_name = 'user_subjects'
              AND constraint_type = 'PRIMARY KEY'
            LIMIT 1
        ");

        if ($primary?->constraint_name) {
            $constraint = str_replace('"', '""', $primary->constraint_name);
            DB::statement("ALTER TABLE \"user_subjects\" DROP CONSTRAINT \"{$constraint}\"");
        }

        Schema::table('user_subjects', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally no-op. Pivot tables do not require an id column.
    }
};
