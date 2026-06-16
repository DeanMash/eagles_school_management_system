<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PromotionController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CriticalRouteRegressionTest extends TestCase
{
    public function test_payment_routes_do_not_use_hash_bound_id_parameter(): void
    {
        $this->assertSame(['year'], Route::getRoutes()->getByName('payments.show')->parameterNames());
        $this->assertSame(['payment_id'], Route::getRoutes()->getByName('payments.edit')->parameterNames());
        $this->assertSame(['payment_id'], Route::getRoutes()->getByName('payments.update')->parameterNames());
        $this->assertSame(['payment_id'], Route::getRoutes()->getByName('payments.destroy')->parameterNames());
    }

    public function test_student_promotion_routes_use_promotion_controller(): void
    {
        foreach ([
            'students.promotion',
            'students.promote_selector',
            'students.promote',
            'students.promotion_manage',
            'students.promotion_reset',
            'students.promotion_reset_all',
        ] as $routeName) {
            $this->assertStringStartsWith(
                PromotionController::class,
                Route::getRoutes()->getByName($routeName)->getActionName()
            );
        }
    }

    public function test_marks_and_pin_routes_required_by_views_are_registered(): void
    {
        $expectedMethods = [
            'marks.update' => 'PUT',
            'marks.batch_update' => 'PUT',
            'marks.comment_update' => 'PUT',
            'marks.skills_update' => 'PUT',
            'marks.year_selector' => 'GET',
            'marks.year_select' => 'POST',
            'marks.print' => 'GET',
            'marks.print_tabulation' => 'GET',
            'pins.index' => 'GET',
            'pins.create' => 'GET',
            'pins.store' => 'POST',
            'pins.enter' => 'GET',
            'pins.verify' => 'POST',
            'pins.destroy' => 'DELETE',
        ];

        foreach ($expectedMethods as $routeName => $method) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "Missing route [{$routeName}]");
            $this->assertContains($method, $route->methods(), "Route [{$routeName}] must accept {$method}");
        }
    }

    public function test_timetable_plain_numeric_routes_avoid_global_id_binding(): void
    {
        $this->assertSame(['tt_id'], Route::getRoutes()->getByName('tt.update')->parameterNames());
        $this->assertSame(['tt_id'], Route::getRoutes()->getByName('tt.delete')->parameterNames());
        $this->assertSame(['ts_id'], Route::getRoutes()->getByName('tt.time_slots.edit')->parameterNames());
        $this->assertSame(['ts_id'], Route::getRoutes()->getByName('tt.time_slots.update')->parameterNames());
        $this->assertSame(['ts_id'], Route::getRoutes()->getByName('tt.time_slots.delete')->parameterNames());
    }
}
