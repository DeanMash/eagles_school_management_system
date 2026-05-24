<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AcademicRouteAuthorizationTest extends TestCase
{
    public function test_marks_management_routes_require_academic_staff(): void
    {
        $this->assertRouteHasMiddleware('marks.selector', 'teamSAT');
        $this->assertRouteHasMiddleware('marks.batch_update', 'teamSAT');
        $this->assertRouteHasMiddleware('marks.update', 'teamSAT');
        $this->assertRouteHasMiddleware('marks.comment_update', 'teamSAT');
        $this->assertRouteHasMiddleware('marks.skills_update', 'teamSAT');
    }

    public function test_timetable_write_routes_require_school_admin(): void
    {
        $this->assertRouteHasMiddleware('tt.store', 'teamSA');
        $this->assertRouteHasMiddleware('tt.time_slots.delete', 'teamSA');
        $this->assertRouteHasMiddleware('ttr.store', 'teamSA');
        $this->assertRouteHasMiddleware('ttr.bulk_store_subjects', 'teamSA');
        $this->assertRouteHasMiddleware('ttr.store_weekly_slots', 'teamSA');
        $this->assertRouteHasMiddleware('ttr.destroy', 'teamSA');
    }

    public function test_marks_static_routes_are_not_shadowed_by_marksheet_show_route(): void
    {
        $this->assertRequestMatchesRoute('GET', '/marks/year-selector/student-hash', 'marks.year_selector');
        $this->assertRequestMatchesRoute('POST', '/marks/year-selected/student-hash', 'marks.year_selected');
        $this->assertRequestMatchesRoute('GET', '/marks/print/student-hash/1/2026', 'marks.print');
        $this->assertRequestMatchesRoute('GET', '/marks/tabulation/print/1/2/3', 'marks.print_tabulation');
    }

    private function assertRouteHasMiddleware(string $routeName, string $middleware): void
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Route [{$routeName}] is not registered.");
        $this->assertContains($middleware, $route->middleware());
    }

    private function assertRequestMatchesRoute(string $method, string $uri, string $routeName): void
    {
        $request = Request::create($uri, $method);
        $route = app('router')->getRoutes()->match($request);

        $this->assertSame($routeName, $route->getName());
    }
}
