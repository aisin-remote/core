<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('development_approval_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('development_mid_id')
                ->nullable()
                ->constrained('developments')
                ->cascadeOnDelete();

            $table->foreignId('development_one_id')
                ->nullable()
                ->constrained('development_ones')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('step_order')->index(); 

            $table->enum('type', ['check', 'approve']); 

            $table->string('role')->index(); 
            $table->string('label');        

            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->timestamp('acted_at')->nullable();

            $table->enum('status', ['pending', 'done', 'revised'])
                ->default('pending')
                ->index();

            $table->timestamps();

            $table->unique(['development_mid_id', 'step_order']);
            $table->unique(['development_one_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('development_approval_steps');
    }
};
