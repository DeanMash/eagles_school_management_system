<?php

namespace Tests\Feature;

use App\Helpers\Qs;
use App\Http\Controllers\SupportTeam\UserController;
use App\Http\Middleware\Custom\Admin;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminPrivilegeAndDashboardAccessTest extends TestCase
{
    public function test_user_store_rejects_admin_creating_privileged_user_types()
    {
        $store = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/SupportTeam/UserController.php')),
            'store'
        );

        $this->assertStringContainsString('Qs::userIsAdmin()', $store);
        $this->assertMatchesRegularExpression(
            '/userTypeModel->level\s*<=\s*2/',
            $store
        );
        $this->assertStringContainsString("__('msg.denied')", $store);

        // Guard must run after type lookup and before user creation.
        $levelPos = strpos($store, 'userTypeModel->level');
        $createPos = strpos($store, '$this->user->create(');
        $this->assertNotFalse($levelPos);
        $this->assertNotFalse($createPos);
        $this->assertLessThan($createPos, $levelPos);
    }

    public function test_user_index_and_store_use_same_admin_level_threshold()
    {
        $source = file_get_contents(app_path('Http/Controllers/SupportTeam/UserController.php'));
        $index = $this->extractMethod($source, 'index');
        $store = $this->extractMethod($source, 'store');

        $this->assertStringContainsString("where('level', '>', 2)", $index);
        $this->assertMatchesRegularExpression('/userTypeModel->level\s*<=\s*2/', $store);
        $this->assertTrue(method_exists(UserController::class, 'store'));
    }

    public function test_admin_middleware_allows_team_sa_roles()
    {
        $source = file_get_contents(app_path('Http/Middleware/Custom/Admin.php'));

        $this->assertStringContainsString('Qs::userIsTeamSA()', $source);
        $this->assertStringNotContainsString('Qs::userIsAdmin()', $source);
        $this->assertTrue(class_exists(Admin::class));
        $this->assertContains('super_admin', Qs::getTeamSA());
        $this->assertContains('admin', Qs::getTeamSA());
    }

    public function test_home_controller_sends_super_admin_to_admin_dashboard_route()
    {
        $home = file_get_contents(app_path('Http/Controllers/HomeController.php'));
        $redirect = $this->extractMethod($home, 'redirectToDashboard');

        $this->assertMatchesRegularExpression(
            "/case\s+'super_admin'\s*:\s*return\s+redirect\(\)->route\('admin\.dashboard'\)/",
            preg_replace('/\s+/', ' ', $redirect)
        );
        $this->assertTrue(Route::has('admin.dashboard'));
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
