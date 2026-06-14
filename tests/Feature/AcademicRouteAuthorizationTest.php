<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AcademicRouteAuthorizationTest extends TestCase
{
    public function test_marks_write_routes_require_academic_staff_middleware()
    {
        foreach ([
            'marks.index',
            'marks.selector',
            'marks.bulk',
            'marks.bulk_select',
            'marks.tabulation',
            'marks.tabulation_select',
            'marks.print_tabulation',
            'marks.batch_fix',
            'marks.batch_update',
            'marks.manage',
            'marks.update',
            'marks.comment_update',
            'marks.skills_update',
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'teamSAT');
        }
    }

    public function test_timetable_write_routes_require_admin_staff_middleware()
    {
        foreach ([
            'tt.store',
            'tt.update',
            'tt.delete',
            'tt.time_slots.store',
            'tt.time_slots.edit',
            'tt.time_slots.update',
            'tt.time_slots.delete',
            'tt.time_slots.use',
            'tt.time_slots.bulk_create',
            'ttr.store',
            'ttr.manage',
            'ttr.grid',
            'ttr.bulk_create_time_slots',
            'ttr.bulk_store_subjects',
            'ttr.store_weekly_slots',
            'ttr.edit',
            'ttr.update',
            'ttr.destroy',
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'teamSA');
        }
    }

    public function test_timetable_numeric_routes_bypass_global_hash_id_binding()
    {
        foreach ([
            'tt.update',
            'tt.delete',
            'tt.time_slots.edit',
            'tt.time_slots.update',
            'tt.time_slots.delete',
        ] as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "Expected route [{$routeName}] to be registered.");
            $this->assertStringNotContainsString('{id}', $route->uri(), "Route [{$routeName}] should not use the globally hash-bound [id] parameter.");
        }
    }

    public function test_marks_auxiliary_routes_used_by_views_are_registered()
    {
        foreach ([
            'marks.year_selector',
            'marks.year_selected',
            'marks.print',
            'marks.print_tabulation',
            'marks.comment_update',
            'marks.skills_update',
        ] as $routeName) {
            $this->assertNotNull(
                Route::getRoutes()->getByName($routeName),
                "Expected route [{$routeName}] to be registered."
            );
        }
    }

    public function test_marks_year_selector_route_is_not_captured_by_marks_show()
    {
        $request = Request::create('/marks/year-selector/student-hash', 'GET');

        $this->assertSame(
            'marks.year_selector',
            Route::getRoutes()->match($request)->getName()
        );
    }

    private function assertRouteHasMiddleware(string $routeName, string $middleware): void
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Expected route [{$routeName}] to be registered.");
        $this->assertContains($middleware, $route->gatherMiddleware(), "Route [{$routeName}] is missing [{$middleware}].");
    }
}
