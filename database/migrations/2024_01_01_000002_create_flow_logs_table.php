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
        $tableName = $prefix . 'flow_logs';
        $flowsTable = $prefix . 'flows';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($flowsTable) {
                $table->id();
                $table->foreignId('flow_id')->constrained($flowsTable)->onDelete('cascade');
                $table->unsignedBigInteger('log_user_id')->nullable();
                $table->json('log_info')->nullable();
                $table->timestamps();

                $table->index('flow_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'flow_logs';

        Schema::dropIfExists($tableName);
    }
};
