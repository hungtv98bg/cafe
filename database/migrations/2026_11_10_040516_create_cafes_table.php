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
        \Illuminate\Support\Facades\DB::statement(
            'CREATE TABLE cafes (
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            address VARCHAR(255) NOT NULL,
            description TEXT NULL,
            location geography(Point, 4326) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )'
        );

        \Illuminate\Support\Facades\DB::statement(
            'CREATE INDEX cafes_location_gist
         ON cafes USING GIST (location)'
        );
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS cafes');
    }
};
