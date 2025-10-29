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
        $experimentsTable = $prefix . 'experiments';
        $flowsTable = $prefix . 'flows';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($experimentsTable, $flowsTable) {
                $table->id();
                // Polymorphic relation to user with custom short index name
                $table->string('experimentable_type');
                $table->unsignedBigInteger('experimentable_id');
                $table->index(['experimentable_type', 'experimentable_id'], 'exp_assign_experimentable_idx');

                $table->foreignId('experiment_id')->constrained($experimentsTable)->onDelete('cascade');
                $table->foreignId('flow_id')->constrained($flowsTable)->onDelete('cascade');
                $table->string('cookie_name')->nullable();
                $table->timestamps();

                $table->index('experiment_id');
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
        $tableName = $prefix . 'experiment_assignments';

        Schema::dropIfExists($tableName);
    }
};
