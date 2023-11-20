<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_timings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('employee_id');
            $table->timestamp('date_time');
            $table->string('status')->comment("
            clock_in => 1
            clock_out => 2
            lunch_in => 3
            lunch_out => 4
            meeting_in => 5
            meeting_out => 6
            break_in => 7
            break_out => 8
            ");
            $table->string("last_active")->nullable();
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
        Schema::dropIfExists('users_timings');
    }
}
