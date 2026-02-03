<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClassroomIdToTimeTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('time_tables', function (Blueprint $table) {
            if (!Schema::hasColumn('time_tables', 'classroom_id')) {
                $table->unsignedInteger('classroom_id')->nullable()->after('subject_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('time_tables', function (Blueprint $table) {
            if (Schema::hasColumn('time_tables', 'classroom_id')) {
                $table->dropColumn('classroom_id');
            }
        });
    }
}
