<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServerTimeColumnToUsersTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_timings', function (Blueprint $table) {
           $table->timestamp('server_time')->nullable();
           $table->time('total_hours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_timings', function (Blueprint $table) {
            $table->dropColumn('server_time');
            $table->dropColumn('total_hours');
        });
    }
}
