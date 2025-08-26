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
        Schema::create('icp_approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('icp_id')->constrained('icp')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order')->index();      // 1,2,3...
            $table->enum('type', ['check', 'approve']);               // check / approve
            $table->string('role');                                   // 'leader','supervisor','gm','director','vpd','president'
            $table->string('label');                                  // teks human readable
            $table->foreignId('actor_id')->nullable()->constrained('employees');
            $table->timestamp('acted_at')->nullable();
            $table->enum('status', ['pending', 'done', 'revised'])->default('pending');
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
        Schema::dropIfExists('icp_approval_steps');
    }
};
