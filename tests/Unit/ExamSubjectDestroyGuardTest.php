<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards against CASCADE data loss when deleting exams or subjects that still
 * have marks, exam records, or timetable rows.
 */
class ExamSubjectDestroyGuardTest extends TestCase
{
    public function test_exam_destroy_refuses_when_marks_records_or_timetables_exist(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/ExamController.php');
        $destroy = $this->methodBody($src, 'destroy');

        $this->assertStringContainsString("Mark::where('exam_id', \$id)->exists()", $destroy);
        $this->assertStringContainsString("ExamRecord::where('exam_id', \$id)->exists()", $destroy);
        $this->assertStringContainsString("TimeTableRecord::where('exam_id', \$id)->exists()", $destroy);
        $this->assertStringContainsString('Cannot delete an exam that has marks', $destroy);
    }

    public function test_subject_destroy_refuses_when_marks_or_timetables_exist(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/SubjectController.php');
        $destroy = $this->methodBody($src, 'destroy');

        $this->assertStringContainsString("Mark::where('subject_id', \$id)->exists()", $destroy);
        $this->assertStringContainsString("TimeTable::where('subject_id', \$id)->exists()", $destroy);
        $this->assertStringContainsString('Cannot delete a subject that has marks', $destroy);
    }

    private function read(string $relative): string
    {
        $path = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$relative;
        $this->assertFileExists($path);
        return file_get_contents($path);
    }

    private function methodBody(string $src, string $method): string
    {
        $pattern = '/function\s+'.preg_quote($method, '/').'\s*\([^{]*\{/';
        $this->assertSame(1, preg_match($pattern, $src, $m, PREG_OFFSET_CAPTURE), "Missing method {$method}");
        $start = $m[0][1] + strlen($m[0][0]);
        $depth = 1;
        $i = $start;
        $len = strlen($src);
        while ($i < $len && $depth > 0) {
            $ch = $src[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
            }
            $i++;
        }
        return substr($src, $start, $i - $start - 1);
    }
}
