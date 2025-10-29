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
        $tableName = config('remote-config.table_prefix', '') . 'experiments';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropIndex(['overwrite_id']);
            $table->dropColumn('overwrite_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('remote-config.table_prefix', '') . 'experiments';

        Schema::table($tableName, function (Blueprint $table) {
            $table->integer('overwrite_id')->nullable()->index()->after('type');
        });
    }
};
