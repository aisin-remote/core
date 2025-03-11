<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('npk')->nullable();
            $table->string('name')->nullable();
            $table->string('identity_number')->unique()->nullable();
            $table->date('birthday_date')->nullable();
            $table->string('photo')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->string('company_name')->nullable();
            $table->string('function')->nullable();
            $table->string('position_name')->nullable();
            $table->date('aisin_entry_date')->nullable();
            $table->integer('working_period')->default(0)->nullable();
            $table->string('company_group')->nullable();
            $table->string('foundation_group')->nullable();
            $table->enum('position', ['GM', 'Manager', 'Coordinator', 'Section Head', 'Supervisor']);
            $table->string('grade')->nullable();
            $table->date('last_promote_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
