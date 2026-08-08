<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards against CASCADE data loss on class/section/payment delete, and against
 * payment update mass-assigning amount/year/class/ref_no.
 */
class ClassSectionPaymentDestroyGuardTest extends TestCase
{
    public function test_class_destroy_refuses_when_students_marks_or_payments_exist(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/MyClassController.php');
        $destroy = $this->methodBody($src, 'destroy');

        $this->assertStringContainsString('StudentRecord::where(\'my_class_id\', $id)->exists()', $destroy);
        $this->assertStringContainsString('Mark::where(\'my_class_id\', $id)->exists()', $destroy);
        $this->assertStringContainsString('Payment::where(\'my_class_id\', $id)->exists()', $destroy);
        $this->assertStringContainsString('Cannot delete a class that has student records', $destroy);
    }

    public function test_section_destroy_refuses_when_students_or_marks_exist(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/SectionController.php');
        $destroy = $this->methodBody($src, 'destroy');

        $this->assertStringContainsString('StudentRecord::where(\'section_id\', $id)->exists()', $destroy);
        $this->assertStringContainsString('Mark::where(\'section_id\', $id)->exists()', $destroy);
        $this->assertStringContainsString('Cannot delete a section that has student records', $destroy);
        // Default-section guard must remain
        $this->assertStringContainsString('isActiveSection', $destroy);
    }

    public function test_payment_update_allow_lists_title_and_description_only(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/PaymentController.php');
        $update = $this->methodBody($src, 'update');

        $this->assertStringContainsString("\$req->only(['title', 'description'])", $update);
        $this->assertStringNotContainsString('$req->all()', $update);
    }

    public function test_payment_destroy_refuses_when_payment_records_exist(): void
    {
        $src = $this->read('app/Http/Controllers/SupportTeam/PaymentController.php');
        $destroy = $this->methodBody($src, 'destroy');

        $this->assertStringContainsString("PaymentRecord::where('payment_id', \$payment->id)->exists()", $destroy);
        $this->assertStringContainsString('Cannot delete a payment that has student fee records', $destroy);
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
