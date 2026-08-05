<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\UserController;
use App\Models\User;
use Tests\TestCase;

class UserUpdatePasswordMassAssignmentTest extends TestCase
{
    public function test_user_update_whitelists_profile_fields_and_excludes_password()
    {
        $update = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/SupportTeam/UserController.php')),
            'update'
        );

        // Must use an allow-list so forged fillable attrs (password, code, …)
        // cannot ride along on a normal profile edit.
        $this->assertStringContainsString('$req->only(', $update);
        $this->assertStringContainsString("'name'", $update);
        $this->assertStringContainsString("'email'", $update);
        $this->assertStringContainsString("'nal_id'", $update);

        $this->assertStringNotContainsString('$req->except(', $update);
        $this->assertStringNotContainsString("'password'", $update);
    }

    public function test_password_changes_remain_super_admin_only_via_reset_pass()
    {
        $controller = file_get_contents(app_path('Http/Controllers/SupportTeam/UserController.php'));
        $ctor = $this->extractMethod($controller, '__construct');
        $reset = $this->extractMethod($controller, 'reset_pass');
        $update = $this->extractMethod($controller, 'update');

        $this->assertStringContainsString("middleware('super_admin'", $ctor);
        $this->assertStringContainsString("'reset_pass'", $ctor);
        $this->assertStringContainsString('Hash::make(', $reset);
        $this->assertStringNotContainsString('Hash::make(', $update);
        $this->assertTrue(method_exists(UserController::class, 'reset_pass'));
    }

    public function test_password_remains_fillable_so_create_and_reset_still_work()
    {
        // The fix is at the update allow-list, not by removing password from
        // fillable (store/reset_pass still need mass assignment).
        $user = new User();
        $this->assertContains('password', $user->getFillable());
        $this->assertTrue(method_exists(UserController::class, 'update'));
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
