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
        Schema::table('live_streams', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('scheduled_at');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dateTime('displayed_at')->nullable()->after('status');
            $table->dateTime('removed_at')->nullable()->after('displayed_at');
            $table->integer('duration_seconds')->nullable()->after('removed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['displayed_at', 'removed_at', 'duration_seconds']);
        });
    }
};
