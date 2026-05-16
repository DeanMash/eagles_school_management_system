<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PromotionController;
use App\Http\Controllers\SupportTeam\StudentRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StudentPromotionRoutesTest extends TestCase
{
    public function test_promotion_routes_resolve_to_promotion_controller_before_student_profile_route()
    {
        $promotionRoute = Route::getRoutes()->match(Request::create('/students/promotion', 'GET'));
        $selectorRoute = Route::getRoutes()->getByName('students.promote_selector');
        $promoteRoute = Route::getRoutes()->match(Request::create('/students/promotion/1/2/3/4', 'POST'));
        $manageRoute = Route::getRoutes()->match(Request::create('/students/promotion/manage', 'GET'));
        $resetRoute = Route::getRoutes()->getByName('students.promotion_reset');

        $this->assertSame(PromotionController::class.'@promotion', $promotionRoute->getActionName());
        $this->assertSame(PromotionController::class.'@selector', $selectorRoute->getActionName());
        $this->assertSame(PromotionController::class.'@promote', $promoteRoute->getActionName());
        $this->assertSame(PromotionController::class.'@manage', $manageRoute->getActionName());
        $this->assertSame(PromotionController::class.'@reset', $resetRoute->getActionName());
        $this->assertSame(['promotion_id'], $resetRoute->parameterNames());
    }

    public function test_student_ajax_district_route_is_not_captured_as_student_id()
    {
        $route = Route::getRoutes()->match(Request::create('/students/get-districts', 'GET'));

        $this->assertSame(StudentRecordController::class.'@getDistricts', $route->getActionName());
    }
}
