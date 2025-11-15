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
        $tableName = $prefix . 'winners';

        Schema::table($tableName, function (Blueprint $table) {
            // Drop foreign key if exists
            $table->dropForeign(['flow_id']);

            // Drop flow_id column
            $table->dropColumn('flow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'winners';
        $flowsTable = $prefix . 'flows';

        Schema::table($tableName, function (Blueprint $table) use ($flowsTable) {
            // Restore flow_id column
            $table->foreignId('flow_id')->nullable()->after('language')->constrained($flowsTable)->onDelete('set null');
        });
    }
};