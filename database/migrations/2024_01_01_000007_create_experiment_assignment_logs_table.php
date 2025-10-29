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
        $tableName = $prefix . 'experiment_assignment_logs';
        $assignmentsTable = $prefix . 'experiment_assignments';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($assignmentsTable) {
                $table->id();
                $table->foreignId('experiment_assignment_id')->constrained($assignmentsTable)->onDelete('cascade');
                // Polymorphic relation to user with custom short index name
                $table->string('experimentable_type');
                $table->unsignedBigInteger('experimentable_id');
                $table->index(['experimentable_type', 'experimentable_id'], 'exp_assign_log_experimentable_idx');

                $table->unsignedBigInteger('experiment_id')->nullable();
                $table->unsignedBigInteger('flow_id')->nullable();
                $table->timestamps();

                $table->index('experiment_assignment_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'experiment_assignment_logs';

        Schema::dropIfExists($tableName);
    }
};
