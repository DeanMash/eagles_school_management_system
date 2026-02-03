<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EnsureEventsEventTypeEnum extends Migration
{
    /**
     * Run the migrations.
     * Ensures event_type ENUM includes all values (fixes "Data truncated" error).
     */
    public function up()
    {
        if (!Schema::hasTable('events')) {
            return;
        }

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
            \Log::warning('EnsureEventsEventTypeEnum: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // No revert needed
    }
}
