<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards the parent/child authorization check on users.show.
 *
 * Qs::userIsMyChild($student_id, $parent_id) must be called with the profile
 * subject as the student and Auth as the parent — never the reverse.
 */
class UserShowParentChildAuthTest extends TestCase
{
    public function test_support_team_user_show_calls_user_is_my_child_with_correct_argument_order(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/UserController.php');
        $show = $this->methodBody($src, 'show');

        $this->assertStringContainsString(
            'Qs::userIsMyChild($user_id, Auth::user()->id)',
            $show,
            'users.show must treat the profile subject as the student and Auth as the parent'
        );
        $this->assertStringNotContainsString(
            'Qs::userIsMyChild(Auth::user()->id, $user_id)',
            $show,
            'Swapped userIsMyChild args allow students to view parent profiles / sibling lists'
        );
    }

    public function test_student_show_and_marks_show_use_same_argument_order(): void
    {
        $studentShow = $this->methodBody(
            $this->read('app/Http/Controllers/SupportTeam/StudentRecordController.php'),
            'show'
        );
        $marksShow = $this->methodBody(
            $this->read('app/Http/Controllers/SupportTeam/MarkController.php'),
            'show'
        );

        $this->assertStringContainsString(
            'Qs::userIsMyChild($data[\'sr\']->user_id, Auth::user()->id)',
            $studentShow
        );
        $this->assertStringContainsString(
            'Qs::userIsMyChild($decoded_student_id, Auth::user()->id)',
            $marksShow
        );
    }

    public function test_helper_signature_documents_student_then_parent(): void
    {
        $src = $this->read('app/Helpers/Qs.php');
        $this->assertMatchesRegularExpression(
            '/function\s+userIsMyChild\s*\(\s*\$student_id\s*,\s*\$parent_id\s*\)/',
            $src
        );
        $this->assertStringContainsString(
            "['user_id' => \$student_id, 'my_parent_id' =>\$parent_id]",
            $src
        );
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
