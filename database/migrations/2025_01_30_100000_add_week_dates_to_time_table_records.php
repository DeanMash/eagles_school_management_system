<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWeekDatesToTimeTableRecords extends Migration
{
    public function up()
    {
        Schema::table('time_table_records', function (Blueprint $table) {
            if (!Schema::hasColumn('time_table_records', 'start_date')) {
                $table->date('start_date')->nullable()->after('year');
            }
            if (!Schema::hasColumn('time_table_records', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down()
    {
        Schema::table('time_table_records', function (Blueprint $table) {
            if (Schema::hasColumn('time_table_records', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('time_table_records', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
}
