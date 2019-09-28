<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('sponsor_id');
            $table->boolean('is_consumed');
            $table->unsignedInteger('consumed_by')->nullable($value = true);
            $table->timestamp('consumed_at')->nullable($value = true);
            $table->string('consumed_meal_name')->nullable($value = true);

            // log
            $table->string('created_by')->nullable($value = true);
            $table->string('updated_by')->nullable($value = true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
