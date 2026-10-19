<?php

namespace Tests\Unit;

require_once __DIR__ . '/../../app/Support/Clock.php';

use App\Support\Clock;
use DateTime;
use PHPUnit\Framework\TestCase;
use TypeError;

class TeacherExportTimeTest extends TestCase
{
    public function test_event_start_time_objects_format_without_strtotime(): void
    {
        $start = new DateTime('2026-10-20 09:00:00');

        $this->assertSame('09:00 AM', Clock::timeLabel($start) ?? 'N/A');
        $this->assertSame('09:00 AM', Clock::timeLabel('09:00:00') ?? 'N/A');
        $this->assertSame('N/A', Clock::timeLabel(null) ?? 'N/A');
        $this->assertSame('N/A', Clock::timeLabel('') ?? 'N/A');
    }

    public function test_strtotime_rejects_datetime_objects_used_by_event_casts(): void
    {
        $this->expectException(TypeError::class);

        strtotime(new DateTime('2026-10-20 09:00:00'));
    }

    public function test_teacher_export_does_not_strtotime_event_start_times(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/app/Http/Controllers/Teacher/ExportController.php');

        $this->assertNotFalse($contents);
        $this->assertStringNotContainsString('strtotime($event->start_time)', $contents);
        $this->assertStringContainsString('Clock::timeLabel($event->start_time)', $contents);
    }
}
