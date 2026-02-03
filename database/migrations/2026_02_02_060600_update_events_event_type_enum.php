<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateEventsEventTypeEnum extends Migration
{
    /**
     * Run the migrations.
     * Fix "Data truncated for column 'event_type'" - update ENUM to include all values.
     */
    public function up()
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        DB::statement("ALTER TABLE `events` MODIFY COLUMN `event_type` ENUM(
            'academic',
            'exam',
            'sports',
            'cultural',
            'holiday',
            'meeting',
            'submission',
            'workshop',
            'event',
            'other'
        ) NOT NULL DEFAULT 'event'");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        DB::statement("ALTER TABLE `events` MODIFY COLUMN `event_type` ENUM(
            'academic',
            'exam',
            'sports',
            'cultural',
            'holiday',
            'meeting'
        ) NOT NULL DEFAULT 'academic'");
    }
}
