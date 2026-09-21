<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Users → Edit for teachers/accountants/librarians used to mint a new
 * CODE/STAFF/{Ym}/{rand} username (and overwrite staff_records.code) on
 * every save, so the login identity issued at create time stopped working.
 */
class UserUpdatePreservesStaffUsernameTest extends TestCase
{
    public function testUpdateKeepsExistingUsernameAndDoesNotMintStaffId(): void
    {
        $src = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/SupportTeam/UserController.php');

        $this->assertNotFalse($src);

        if (!preg_match('/function\s+update\s*\([^)]*\)\s*\{/', $src, $match, PREG_OFFSET_CAPTURE)) {
            $this->fail('Could not find UserController::update');
        }

        $start = $match[0][1];
        $brace = strpos($src, '{', $start);
        $depth = 0;
        $len = strlen($src);
        $update = null;

        for ($i = $brace; $i < $len; $i++) {
            $ch = $src[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    $update = substr($src, $start, $i - $start + 1);
                    break;
                }
            }
        }

        $this->assertNotNull($update);

        $this->assertStringContainsString('$data[\'username\'] = $user->username;', $update);
        $this->assertStringNotContainsString('mt_rand(1000, 9999)', $update);
        $this->assertStringNotContainsString('/STAFF/', $update);
        $this->assertStringNotContainsString('$d2[\'code\'] = $data[\'username\'];', $update);
    }
}
