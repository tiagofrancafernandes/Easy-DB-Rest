<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('connection_team', function (Blueprint $table) {
            $table->foreignUuid('connection_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();
            $table->string('permission')->default('view');
            $table->timestamps();
            $table->primary(['connection_id', 'team_id']);
        });

        Schema::create('snippet_team', function (Blueprint $table) {
            $table->foreignUuid('snippet_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();
            $table->string('permission')->default('view');
            $table->timestamps();
            $table->primary(['snippet_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snippet_team');
        Schema::dropIfExists('connection_team');
    }
};
