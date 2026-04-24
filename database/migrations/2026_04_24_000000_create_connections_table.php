<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('connections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('driver', 20);
            $table->string('host')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('database');
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('schema')->nullable();
            $table->unsignedSmallInteger('timeout')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
