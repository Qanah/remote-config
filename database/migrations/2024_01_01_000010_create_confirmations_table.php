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
        $experimentsTable = $prefix . 'experiments';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($experimentsTable) {
                $table->id();
                // Polymorphic relation to user with custom short index name
                $table->string('experimentable_type');
                $table->unsignedBigInteger('experimentable_id');
                $table->index(['experimentable_type', 'experimentable_id'], 'confirmations_experimentable_idx');

                $table->foreignId('experiment_id')->nullable()->constrained($experimentsTable)->onDelete('set null');
                $table->string('experiment_name');
                $table->string('status')->default('pending')->index();
                $table->text('metadata')->nullable();
                $table->timestamps();

                $table->index(['experiment_name', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'confirmations';

        Schema::dropIfExists($tableName);
    }
};
