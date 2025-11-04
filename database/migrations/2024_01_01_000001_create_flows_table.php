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
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'flows';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('type')->index();
                $table->string('name'); // Unique flow name within type
                $table->json('content')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('is_default')->default(false)->index();
                $table->string('default_type')->nullable(); // Helper column for MySQL unique constraint
                $table->timestamps();

                // Unique constraint: only one flow name per type
                $table->unique(['type', 'name'], 'flows_type_name_unique');

                // Unique constraint: only one default flow per type (MySQL-compatible)
                // NULL values are ignored, so only default flows are constrained
                $table->unique('default_type', 'flows_default_type_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'flows';

        Schema::dropIfExists($tableName);
    }
};
