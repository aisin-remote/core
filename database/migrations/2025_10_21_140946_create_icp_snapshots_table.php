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
        Schema::create('icp_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('icp_id')->constrained('icp')->cascadeOnDelete();
            $table->unsignedSmallInteger('plan_year')->nullable();
            $table->string('reason')->nullable();
            $table->json('icp')->nullable();
            $table->json('details')->nullable();
            $table->json('steps')->nullable();
            $table->foreignId('created_by')->constrained('employees')->nullable();
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
        Schema::dropIfExists('icp_snapshots');
    }
};
