<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('idp', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->unsignedBigInteger('assessment_id')->nullable(); // Foreign key
            $table->string('category');
            $table->text('development_program');
            $table->text('development_target');
            $table->date('date');
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('idp');
    }
};
