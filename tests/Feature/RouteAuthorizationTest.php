<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteAuthorizationTest extends TestCase
{
    public function test_marks_management_routes_require_academic_staff(): void
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
            $this->assertRouteHasMiddleware($routeName, 'teamSAT');
        }
    }

    public function test_marks_result_routes_remain_available_to_authenticated_users(): void
    {
        foreach ([
            'marks.show',
            'marks.year_selector',
            'marks.year_selected',
            'marks.print_view',
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'auth');
            $this->assertRouteMissingMiddleware($routeName, 'teamSAT');
        }
    }

    public function test_marks_static_routes_are_not_shadowed_by_result_route(): void
    {
        $this->assertSame(
            'marks.year_selector',
            Route::getRoutes()->match(Request::create('/marks/year-selector/student-hash', 'GET'))->getName()
        );

        $this->assertSame(
            'marks.print_view',
            Route::getRoutes()->match(Request::create('/marks/print/student-hash/1/2026', 'GET'))->getName()
        );
    }

    public function test_timetable_mutation_routes_require_support_admins(): void
    {
        foreach ([
            'tt.store',
            'tt.update',
            'tt.delete',
            'tt.time_slots.store',
            'tt.time_slots.edit',
            'tt.time_slots.update',
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
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'teamSA');
        }
    }

    public function test_timetable_destructive_routes_require_super_admins(): void
    {
        foreach ([
            'tt.time_slots.delete',
            'ttr.destroy',
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'super_admin');
        }
    }

    public function test_timetable_read_routes_remain_available_to_authenticated_users(): void
    {
        foreach ([
            'tt.index',
            'ttr.index',
            'ttr.print',
            'ttr.show',
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'auth');
            $this->assertRouteMissingMiddleware($routeName, 'teamSA');
            $this->assertRouteMissingMiddleware($routeName, 'super_admin');
        }
    }

    private function assertRouteHasMiddleware(string $routeName, string $middleware): void
    {
        $this->assertContains($middleware, $this->routeMiddleware($routeName), "Route [{$routeName}] is missing [{$middleware}] middleware.");
    }

    private function assertRouteMissingMiddleware(string $routeName, string $middleware): void
    {
        $this->assertNotContains($middleware, $this->routeMiddleware($routeName), "Route [{$routeName}] unexpectedly has [{$middleware}] middleware.");
    }

    private function routeMiddleware(string $routeName): array
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Route [{$routeName}] is not registered.");

        return $route->gatherMiddleware();
    }
}
