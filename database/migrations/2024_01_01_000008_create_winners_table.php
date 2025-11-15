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
        $tableName = $prefix . 'winners';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('type')->index();
                $table->json('content');
                $table->string('platform')->index();
                $table->string('country_code')->index();
                $table->string('language')->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();

                $table->index(['platform', 'country_code', 'language']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('remote-config.table_prefix', '');
        $tableName = $prefix . 'winners';

        Schema::dropIfExists($tableName);
    }
};
