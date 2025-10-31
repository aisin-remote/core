<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ubah ENUM menjadi draft, submitted, approved, revised, checked
        DB::statement("ALTER TABLE activity_plans MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'revised', 'checked') NOT NULL DEFAULT 'draft'");
    }

    public function down()
    {
        // Balik ke enum awal (kalau di-rollback)
        DB::statement("ALTER TABLE activity_plans MODIFY COLUMN status ENUM('draft', 'submitted', 'approved') NOT NULL DEFAULT 'draft'");
    }
};
