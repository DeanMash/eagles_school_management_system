<?php

namespace Tests\Unit;

require_once __DIR__ . '/../../app/Support/Clock.php';

use App\Support\Clock;
use DateTime;
use PHPUnit\Framework\TestCase;
use TypeError;

class ClockTest extends TestCase
{
    public function test_formats_datetime_objects_the_same_as_time_strings(): void
    {
        $clock = new DateTime('2026-10-20 15:00:00');

        $this->assertSame(date('h:i A', strtotime('15:00:00')), Clock::timeLabel($clock));
        $this->assertSame(date('D, j F, Y', strtotime('2026-10-20 15:00:00')), Clock::dateLabel($clock));
        $this->assertSame('09:00 AM', Clock::timeLabel('09:00:00'));
        $this->assertNull(Clock::timeLabel(null));
        $this->assertNull(Clock::dateLabel(null));
    }

    public function test_strtotime_rejects_datetime_objects(): void
    {
        $this->expectException(TypeError::class);

        strtotime(new DateTime('2026-10-20 09:00:00'));
    }

    public function test_event_and_receipt_views_do_not_strtotime_cast_dates(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            'app/Models/Event.php',
            'resources/views/pages/teacher/dashboard.blade.php',
            'resources/views/pages/support_team/dashboard.blade.php',
            'resources/views/pages/support_team/payments/receipt.blade.php',
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($root . '/' . $file);
            $this->assertNotFalse($contents, $file);
            $this->assertStringNotContainsString('strtotime($event->start_time)', $contents, $file);
            $this->assertStringNotContainsString('strtotime($this->start_time)', $contents, $file);
            $this->assertStringNotContainsString('strtotime($r->created_at)', $contents, $file);
        }
    }
}
