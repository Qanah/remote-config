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
        $tableName = $prefix . 'experiment_flow';
        $experimentsTable = $prefix . 'experiments';
        $flowsTable = $prefix . 'flows';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($experimentsTable, $flowsTable) {
                $table->id();
                $table->foreignId('experiment_id')->constrained($experimentsTable)->onDelete('cascade');
                $table->foreignId('flow_id')->constrained($flowsTable)->onDelete('cascade');
                $table->integer('ratio')->default(50);
                $table->timestamps();

                $table->index(['experiment_id', 'flow_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'experiment_flow';

        Schema::dropIfExists($tableName);
    }
};
