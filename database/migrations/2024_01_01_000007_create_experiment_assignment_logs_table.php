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
                $table->morphs('experimentable'); // Polymorphic relation to user
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
