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
        $tableName = $prefix . 'confirmations';
        $flowsTable = $prefix . 'flows';

        Schema::table($tableName, function (Blueprint $table) use ($flowsTable) {
            // Add flow_id column
            $table->foreignId('flow_id')->nullable()->after('experiment_id')->constrained($flowsTable)->onDelete('set null');

            // Remove experiment_name column (we have experiment_id)
            $table->dropColumn('experiment_name');

            // Drop old index
            $table->dropIndex(['experiment_name', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'confirmations';

        Schema::table($tableName, function (Blueprint $table) {
            // Remove flow_id
            $table->dropForeign(['flow_id']);
            $table->dropColumn('flow_id');

            // Restore experiment_name
            $table->string('experiment_name')->after('experiment_id');

            // Restore old index
            $table->index(['experiment_name', 'status']);
        });
    }
};