<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('connections', function (Blueprint $table): void {
            $table->string('database')->nullable()->change();

            $table->string('url')->nullable()->after('driver');
            $table->string('charset')->nullable()->after('password');
            $table->string('collation')->nullable()->after('charset');
            $table->string('prefix')->nullable()->after('collation');
            $table->string('search_path')->nullable()->after('prefix');
            $table->string('sslmode')->nullable()->after('search_path');
        });
    }

    public function down(): void
    {
        Schema::table('connections', function (Blueprint $table): void {
            $table->dropColumn([
                'url',
                'charset',
                'collation',
                'prefix',
                'search_path',
                'sslmode',
            ]);

            // Reverting database to not nullable might fail if there are nulls,
            // but this is standard procedure.
            $table->string('database')->nullable(false)->change();
        });
    }
};
