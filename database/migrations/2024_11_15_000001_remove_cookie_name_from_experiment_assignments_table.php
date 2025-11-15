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
        $tableName = $prefix . 'experiment_assignments';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('cookie_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'experiment_assignments';

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('cookie_name')->nullable()->after('flow_id');
        });
    }
};