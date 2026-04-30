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
        Schema::create('http_filter_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('method', 10)->nullable();
            $table->string('domain')->nullable();
            $table->string('url')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->decimal('time', 5)->nullable();
            $table->unsignedSmallInteger('code')->nullable();
            $table->dateTime('created_at');

            $table->index(['ip'], 'ip');
            $table->index(['ip', 'created_at'], 'ip,created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('http_filter_requests');
    }
};
