<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixEventsEventTypeEnum extends Migration
{
    /**
     * Run the migrations.
     * Fix "Data truncated for column 'event_type'" - ensure ENUM includes all values.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        // Fix "Data truncated for column 'event_type'" - ensure ENUM includes all values used by the app
        try {
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
        } catch (\Exception $e) {
            \Log::warning('FixEventsEventTypeEnum: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to minimal ENUM if needed (optional)
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
