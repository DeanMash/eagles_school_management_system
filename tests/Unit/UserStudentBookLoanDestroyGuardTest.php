<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards against CASCADE wipe of outstanding book_transactions (and the resulting
 * books.available_copies corruption) when deleting users or students.
 */
class UserStudentBookLoanDestroyGuardTest extends TestCase
{
    public function test_user_destroy_refuses_when_outstanding_loans_as_borrower_or_issuer(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/UserController.php');
        $destroy = $this->methodBody($src, 'destroy');

        $this->assertStringContainsString('BookTransaction::where(\'status\', \'issued\')', $destroy);
        $this->assertStringContainsString("where('student_id', \$id)", $destroy);
        $this->assertStringContainsString("orWhere('issued_by', \$id)", $destroy);
        $this->assertStringContainsString('Cannot delete a user with outstanding library loans', $destroy);
        $this->assertStringContainsString('$this->user->delete($user->id)', $destroy);
    }

    public function test_student_destroy_refuses_when_outstanding_loans_exist(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/StudentRecordController.php');
        $destroy = $this->methodBody($src, 'destroy');

        $this->assertStringContainsString('BookTransaction::where(\'status\', \'issued\')->where(\'student_id\', $st_id)->exists()', $destroy);
        $this->assertStringContainsString('Cannot delete a student with outstanding library loans', $destroy);
        $this->assertStringContainsString('$this->user->delete($sr->user->id)', $destroy);
    }

    public function test_book_transactions_fk_cascades_on_user_delete(): void
    {
        $src = $this->read('database/migrations/2026_01_20_095936_create_book_transactions_table.php');

        $this->assertStringContainsString("foreignId('student_id')->constrained('users')->onDelete('cascade')", $src);
        $this->assertStringContainsString("foreignId('issued_by')->constrained('users')->onDelete('cascade')", $src);
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
