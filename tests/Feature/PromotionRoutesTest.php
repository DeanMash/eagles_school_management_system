<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PromotionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PromotionRoutesTest extends TestCase
{
    public function testPromotionRoutesResolveToPromotionController()
    {
        $this->assertRouteAction('GET', '/students/promotion', 'promotion');
        $this->assertRouteAction('GET', '/students/promotion/1/2/3/4', 'promotion');
        $this->assertRouteAction('POST', '/students/promotion/selector', 'selector');
        $this->assertRouteAction('POST', '/students/promotion/1/2/3/4', 'promote');
        $this->assertRouteAction('GET', '/students/promotion/manage', 'manage');
        $this->assertRouteAction('DELETE', '/students/promotion/reset-all', 'reset_all');
        $this->assertRouteAction('DELETE', '/students/promotion/reset/5', 'reset');

        $this->assertSame('/students/promotion/1/2/3/4', route('students.promote', [1, 2, 3, 4], false));
        $this->assertSame('/students/promotion/selector', route('students.promote_selector', [], false));
    }

    public function testPromotionResetRouteDoesNotUseGlobalIdBindingParameter()
    {
        $route = Route::getRoutes()->match(Request::create('/students/promotion/reset/5', 'DELETE'));

        $this->assertSame(['promotion_id'], $route->parameterNames());
    }

    private function assertRouteAction(string $method, string $uri, string $actionMethod): void
    {
        $route = Route::getRoutes()->match(Request::create($uri, $method));

        $this->assertSame(PromotionController::class . '@' . $actionMethod, $route->getActionName());
    }
}
