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
        Schema::create('http_filter_ips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip', 50)->nullable();
            $table->unsignedTinyInteger('is_blocked')->default(0);
            $table->dateTime('blocked_at')->nullable();
            $table->dateTime('block_expire_at')->nullable();
            $table->timestamps();

            $table->index(['ip'], 'ip');
            $table->index(['ip', 'created_at'], 'ip,created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('http_filter_ips');
    }
};
