<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('iso_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('dob')->nullable();
            $table->tinyInteger('gender')->nullable()->comment('1 =>Male, 0=>Female');
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('height')->nullable();
            $table->integer('notification')->default(1)->comment('0 =>off, 1=>on');
            $table->tinyInteger('role')->default(2)->comments('1 =>admin, 2=>employee');
            $table->tinyInteger('status')->default(1)->comment='0 =>inactive, 1=>active';
            $table->tinyInteger('type')->default(0)->comments("0 =>Pending, 1=>Approve,2=>Reject");
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('email_verification_otp')->nullable();
            $table->tinyInteger('device_type')->default(1)->comment='0 =>IOS, 1=>Android';
            $table->text('fcm_token')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->foreignId('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
