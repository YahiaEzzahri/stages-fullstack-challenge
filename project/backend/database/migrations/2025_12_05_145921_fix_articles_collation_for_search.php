<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir toute la table
        DB::statement('ALTER TABLE articles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // Convertir les colonnes
        DB::statement('ALTER TABLE articles MODIFY title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
        DB::statement('ALTER TABLE articles MODIFY content TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE articles MODIFY title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
        DB::statement('ALTER TABLE articles MODIFY content TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');

        DB::statement('ALTER TABLE articles CONVERT TO CHARACTER SET latin1 COLLATE latin1_general_ci');
    }
};
