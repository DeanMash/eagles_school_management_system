<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Student/teacher dashboards used unqualified Carbon::parse().
 * config/app.php does not alias Carbon, so PHP looks up \Carbon in the
 * global namespace and fatals with "Class 'Carbon' not found".
 *
 * Trigger: a student or teacher with timetable entries today hits
 * /home (password-reset redirect and RedirectIfAuthenticated), which
 * routes to student.dashboard / teacher.dashboard.
 */
class DashboardCarbonNamespaceTest extends TestCase
{
    public function testStudentDashboardQualifiesCarbonParse()
    {
        $path = dirname(__DIR__, 2) . '/resources/views/pages/support_team/dashboard.blade.php';
        $this->assertFileExists($path);
        $contents = file_get_contents($path);

        $this->assertStringContainsString('\\Carbon\\Carbon::parse', $contents);
        $this->assertSame(0, preg_match('/(?<!\\\\)Carbon::parse\s*\(/', $contents));
    }

    public function testTeacherDashboardQualifiesCarbonParse()
    {
        $path = dirname(__DIR__, 2) . '/resources/views/pages/teacher/dashboard.blade.php';
        $this->assertFileExists($path);
        $contents = file_get_contents($path);

        $this->assertStringContainsString('\\Carbon\\Carbon::parse', $contents);
        $this->assertSame(0, preg_match('/(?<!\\\\)Carbon::parse\s*\(/', $contents));
    }
}
