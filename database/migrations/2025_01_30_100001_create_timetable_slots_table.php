<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimetableSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ttr_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('subject_id')->nullable();
            $table->unsignedInteger('teacher_id')->nullable();
            $table->unsignedInteger('classroom_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('ttr_id')->references('id')->on('time_table_records')->onDelete('cascade');
            $table->unique(['ttr_id', 'date', 'start_time', 'classroom_id'], 'timetable_slots_unique_classroom');
            $table->unique(['ttr_id', 'date', 'start_time', 'teacher_id'], 'timetable_slots_unique_teacher');
        });
    }

    public function down()
    {
        Schema::dropIfExists('timetable_slots');
    }
}
