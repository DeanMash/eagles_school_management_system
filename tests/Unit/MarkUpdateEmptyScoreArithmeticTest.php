<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Newly created mark rows render empty number inputs. ConvertEmptyStringsToNull
 * turns those into null, and PHP 8.1+ TypeErrors on null + null in MarkController::update.
 */
class MarkUpdateEmptyScoreArithmeticTest extends TestCase
{
    public function testUpdateCastsEmptyCaAndExamScoresBeforeAdding(): void
    {
        $src = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/SupportTeam/MarkController.php');

        $this->assertNotFalse($src);
        $this->assertStringContainsString('$d[\'t1\'] = $t1 = $mks[\'t1_\'.$mk->id] ?? null;', $src);
        $this->assertStringContainsString('$d[\'t2\'] = $t2 = $mks[\'t2_\'.$mk->id] ?? null;', $src);
        $this->assertStringContainsString('$d[\'exm\'] = $exm = $mks[\'exm_\'.$mk->id] ?? null;', $src);
        $this->assertStringContainsString('$d[\'tca\'] = $tca = (int) $t1 + (int) $t2;', $src);
        $this->assertStringContainsString('$total = $tca + (int) $exm;', $src);
        $this->assertStringNotContainsString('$d[\'tca\'] = $tca = $t1 + $t2;', $src);
        $this->assertStringNotContainsString('$total = $tca + $exm;', $src);
    }
}
