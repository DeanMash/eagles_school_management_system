<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Librarian issueForm() uses whereHas('studentRecord'). Laravel resolves that
 * name as a method, not as snake_case student_record(), so a missing alias
 * 500s the issue-book page and blocks lending.
 */
class UserStudentRecordRelationTest extends TestCase
{
    public function test_user_model_defines_studentRecord_hasone_alias(): void
    {
        $src = $this->read('app/Models/User.php');
        $body = $this->methodBody($src, 'studentRecord');

        $this->assertStringContainsString('hasOne(StudentRecord::class)', $body);
        $this->assertStringContainsString('function student_record()', $src);
    }

    public function test_issue_form_wherehas_targets_studentRecord_relation(): void
    {
        $src = $this->read('app/Http/Controllers/Librarian/BookController.php');
        $issueForm = $this->methodBody($src, 'issueForm');

        $this->assertStringContainsString("where('user_type', 'student')", $issueForm);
        $this->assertMatchesRegularExpression("/whereHas\\('studentRecord'\\)/", $issueForm);
    }

    public function test_event_scopes_use_studentRecord_property(): void
    {
        $src = $this->read('app/Models/Event.php');

        $this->assertStringContainsString('$student->studentRecord', $src);
        $this->assertStringContainsString('$user->studentRecord', $src);
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
