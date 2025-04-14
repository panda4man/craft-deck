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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // vanilla, spigot, paper, etc.
            $table->string('version')->nullable();
            $table->string('path'); // e.g., /home/minecraft/servers/myserver
            $table->string('jar_file')->nullable(); // path to server jar
            $table->integer('ram_mb')->default(1024);
            $table->enum('status', ['stopped', 'running', 'error'])->default('stopped');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
