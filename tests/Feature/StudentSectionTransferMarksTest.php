<?php

namespace Tests\Feature;

use App\Helpers\Mk;
use App\Repositories\MarkRepo;
use Tests\TestCase;

class StudentSectionTransferMarksTest extends TestCase
{
    public function test_delete_old_record_accepts_section_and_mismatches_on_section()
    {
        $source = file_get_contents(app_path('Helpers/Mk.php'));
        $method = $this->extractMethod($source, 'deleteOldRecord');

        $this->assertStringContainsString('$section_id = null', $method);
        $this->assertStringContainsString("orWhere('section_id'", $method);
        $this->assertStringContainsString("where('my_class_id', '<>', \$class_id)", $method);

        // Must not only filter by class — that leaves same-class prior-section rows.
        $this->assertDoesNotMatchRegularExpression(
            '/Mark::where\(\s*[\'"]my_class_id[\'"]\s*,\s*[\'"]<>[\'"]\s*,\s*\$class_id\s*\)\s*->where\(\$d\)\s*;/',
            $method
        );
    }

    public function test_student_update_passes_section_id_into_delete_old_record()
    {
        $update = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/SupportTeam/StudentRecordController.php')),
            'update'
        );

        $this->assertStringContainsString('Mk::deleteOldRecord(', $update);
        $this->assertStringContainsString("\$studentData['section_id']", $update);
        $this->assertStringContainsString("\$studentData['my_class_id']", $update);
    }

    public function test_exam_total_aggregates_without_section_so_stale_section_rows_corrupt_totals()
    {
        // Documents the blast radius: after a same-class section move, any
        // leftover mark rows for the old section are summed into term totals.
        $total = $this->extractMethod(
            file_get_contents(app_path('Repositories/MarkRepo.php')),
            'getExamTotalTerm'
        );

        $this->assertStringContainsString("'my_class_id' => \$class_id", $total);
        $this->assertStringContainsString("'year' => \$year", $total);
        $this->assertStringNotContainsString('section_id', $total);
        $this->assertStringContainsString('sum($tex)', $total);

        $this->assertTrue(method_exists(Mk::class, 'deleteOldRecord'));
        $this->assertTrue(method_exists(MarkRepo::class, 'getExamTotalTerm'));
    }

    private function extractMethod(string $source, string $method): string
    {
        if (!preg_match(
            '/function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*\{/',
            $source,
            $match,
            PREG_OFFSET_CAPTURE
        )) {
            $this->fail("Could not find method {$method}");
        }

        $start = $match[0][1];
        $brace = strpos($source, '{', $start);
        $depth = 0;
        $len = strlen($source);

        for ($i = $brace; $i < $len; $i++) {
            $ch = $source[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }

        $this->fail("Could not extract method body for {$method}");
    }
}
