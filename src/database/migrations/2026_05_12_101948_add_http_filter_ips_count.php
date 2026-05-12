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
        Schema::table('http_filter_ips', function (Blueprint $table) {
            $table->unsignedBigInteger('requests_count')->default(1)->after('ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('http_filter_ips', function (Blueprint $table) {
            $table->dropColumn('requests_count');
        });
    }
};
