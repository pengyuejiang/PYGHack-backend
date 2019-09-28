<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_template', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('owner_id')->nullable($value = true);
            $table->json('body')->nullable($value = true);

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
        Schema::dropIfExists('survey_template');
    }
}