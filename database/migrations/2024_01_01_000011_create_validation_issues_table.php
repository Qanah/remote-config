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
        $tableName = $prefix . 'validation_issues';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->morphs('experimentable'); // Polymorphic relation to user
                $table->string('platform')->nullable()->index();
                $table->string('path')->index();
                $table->text('invalid_value');
                $table->string('type')->nullable()->index();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'validation_issues';

        Schema::dropIfExists($tableName);
    }
};
