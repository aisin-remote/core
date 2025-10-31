<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ubah enum menjadi lebih lengkap
        DB::statement("ALTER TABLE activity_plan_items MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'revised', 'checked') NOT NULL DEFAULT 'draft'");
    }

    public function down()
    {
        // Kembalikan ke enum lama saat rollback
        DB::statement("ALTER TABLE activity_plan_items MODIFY COLUMN status ENUM('draft', 'submitted', 'approved') NOT NULL DEFAULT 'draft'");
    }
};
