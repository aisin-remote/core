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
        Schema::create('ipp_approval_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ipp_id')
                ->constrained('ipps')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('step_order')->index();
            $table->enum('type', ['check', 'approve']);
            $table->string('role');
            $table->string('label');

            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('employees');

            $table->timestamp('acted_at')->nullable();

            $table->enum('status', ['pending', 'done', 'revised'])
                ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ipp_approval_steps');
    }
};
