<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteAuthorizationTest extends TestCase
{
    public function test_mark_mutation_routes_require_academic_staff_middleware()
    {
        foreach ([
            'marks.index',
            'marks.selector',
            'marks.bulk',
            'marks.bulk_select',
            'marks.tabulation',
            'marks.tabulation_select',
            'marks.batch_fix',
            'marks.batch_update',
            'marks.manage',
            'marks.update',
        ] as $routeName) {
            $middleware = $this->middlewareFor($routeName);

            $this->assertContains('auth', $middleware, "{$routeName} should require authentication.");
            $this->assertContains('teamSAT', $middleware, "{$routeName} should require academic staff access.");
        }
    }

    public function test_mark_read_routes_remain_available_for_authorized_students_and_parents()
    {
        foreach ([
            'marks.show',
            'marks.year_selector',
            'marks.year_selected',
            'marks.print_view',
        ] as $routeName) {
            $middleware = $this->middlewareFor($routeName);

            $this->assertContains('auth', $middleware, "{$routeName} should require authentication.");
            $this->assertNotContains('teamSAT', $middleware, "{$routeName} should keep controller-level student/parent checks.");
        }
    }

    public function test_timetable_management_routes_require_admin_staff_middleware()
    {
        foreach ([
            'tt.index',
            'tt.store',
            'tt.update',
            'tt.delete',
            'tt.time_slots.store',
            'tt.time_slots.edit',
            'tt.time_slots.update',
            'tt.time_slots.delete',
            'tt.time_slots.use',
            'tt.time_slots.bulk_create',
            'ttr.index',
            'ttr.store',
            'ttr.manage',
            'ttr.grid',
            'ttr.bulk_create_time_slots',
            'ttr.bulk_store_subjects',
            'ttr.store_weekly_slots',
            'ttr.print',
            'ttr.edit',
            'ttr.update',
            'ttr.destroy',
            'ttr.show',
        ] as $routeName) {
            $middleware = $this->middlewareFor($routeName);

            $this->assertContains('auth', $middleware, "{$routeName} should require authentication.");
            $this->assertContains('teamSA', $middleware, "{$routeName} should require admin staff access.");
        }
    }

    public function test_marks_year_selector_route_is_not_shadowed_by_marksheet_show_route()
    {
        $route = Route::getRoutes()->match(Request::create('/marks/year-selector/student-hash', 'GET'));

        $this->assertSame('marks.year_selector', $route->getName());
    }

    private function middlewareFor(string $routeName): array
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Route {$routeName} should exist.");

        return $route->gatherMiddleware();
    }
}
