<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->string('npk')->primary();
            $table->string('name');
            $table->string('identity_number')->unique();
            $table->date('birthday_date');
            $table->string('photo')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->string('company_name');
            $table->string('function');
            $table->string('position_name');
            $table->date('aisin_entry_date');
            $table->integer('working_period')->default(0);
            $table->string('company_group');
            $table->string('foundation_group');
            $table->string('position');
            $table->string('grade');
            $table->date('last_promote_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
