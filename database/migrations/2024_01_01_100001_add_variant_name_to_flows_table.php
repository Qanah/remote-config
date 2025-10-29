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
        $tableName = config('remote-config.table_prefix', '') . 'flows';

        Schema::table($tableName, function (Blueprint $table) {
            // Add variant_name column (nullable initially for migration)
            $table->string('variant_name')->nullable()->after('type');
        });

        // Migrate existing data: convert overwrite_id to variant_name
        DB::table($tableName)->get()->each(function ($flow) use ($tableName) {
            $variantName = $flow->overwrite_id
                ? 'variant-' . $flow->overwrite_id
                : 'default';

            DB::table($tableName)
                ->where('id', $flow->id)
                ->update(['variant_name' => $variantName]);
        });

        // Make variant_name non-nullable and add unique constraint
        Schema::table($tableName, function (Blueprint $table) {
            $table->string('variant_name')->nullable(false)->change();
            $table->unique(['type', 'variant_name'], 'flows_type_variant_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('remote-config.table_prefix', '') . 'flows';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropUnique('flows_type_variant_unique');
            $table->dropColumn('variant_name');
        });
    }
};
