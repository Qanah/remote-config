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
        $tableName = $prefix . 'experiment_logs';
        $experimentsTable = $prefix . 'experiments';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($experimentsTable) {
                $table->id();
                $table->foreignId('experiment_id')->constrained($experimentsTable)->onDelete('cascade');
                $table->unsignedBigInteger('log_user_id')->nullable();
                $table->json('log_info')->nullable();
                $table->timestamps();

                $table->index('experiment_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'experiment_logs';

        Schema::dropIfExists($tableName);
    }
};
