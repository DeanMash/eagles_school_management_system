<?php

namespace Tests\Feature;

use App\Http\Controllers\EventController;
use App\Http\Controllers\SupportTeam\MarkController;
use App\Http\Controllers\SupportTeam\PromotionController;
use Illuminate\Http\Request;
use Tests\TestCase;

class CriticalRouteRegressionTest extends TestCase
{
    public function test_dashboard_entry_points_require_authentication(): void
    {
        $this->assertRouteHasMiddleware('home', 'auth');
        $this->assertRouteHasMiddleware('dashboard', 'auth');
    }

    public function test_mark_mutation_routes_require_academic_staff(): void
    {
        foreach ([
            'marks.selector',
            'marks.batch_update',
            'marks.update',
            'marks.comment_update',
            'marks.skills_update',
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'auth');
            $this->assertRouteHasMiddleware($routeName, 'teamSAT');
        }
    }

    public function test_mark_read_routes_are_not_shadowed_or_staff_only(): void
    {
        $this->assertSame('marks.year_selector', $this->routeNameFor('GET', '/marks/year-selector/student-hash'));
        $this->assertSame('marks.show', $this->routeNameFor('GET', '/marks/student-hash/2026'));

        $this->assertRouteHasMiddleware('marks.year_selector', 'auth');
        $this->assertRouteDoesNotHaveMiddleware('marks.year_selector', 'teamSAT');
        $this->assertRouteHasMiddleware('marks.show', 'auth');
        $this->assertRouteDoesNotHaveMiddleware('marks.show', 'teamSAT');
    }

    public function test_student_promotion_and_status_routes_are_staff_only_and_correctly_wired(): void
    {
        $this->assertSame('students.promotion', $this->routeNameFor('GET', '/students/promotion'));
        $this->assertSame('students.promote_selector', $this->routeNameFor('POST', '/students/promotion/selector'));
        $this->assertSame('students.promotion_manage', $this->routeNameFor('GET', '/students/promotion/manage'));

        foreach ([
            'students.promotion',
            'students.promote_selector',
            'students.promote',
            'students.promotion_manage',
            'students.promotion_reset',
            'students.promotion_reset_all',
            'students.not_graduated',
        ] as $routeName) {
            $this->assertRouteHasMiddleware($routeName, 'auth');
            $this->assertRouteHasMiddleware($routeName, 'teamSA');
        }

        $this->assertRouteUsesController('students.promotion', PromotionController::class, 'promotion');
        $this->assertRouteUsesController('students.promote', PromotionController::class, 'promote');
        $this->assertRouteUsesController('students.promotion_manage', PromotionController::class, 'manage');
    }

    public function test_hash_decoding_routes_avoid_the_global_id_binding(): void
    {
        $this->assertSame(['user'], $this->routeParameterNames('user.view'));
        $this->assertSame(['user'], $this->routeParameterNames('users.show'));
        $this->assertSame(['sr_id'], $this->routeParameterNames('students.show'));
        $this->assertSame(['sr_id'], $this->routeParameterNames('students.not_graduated'));
        $this->assertSame(['event'], $this->routeParameterNames('events.show'));
    }

    public function test_calendar_and_librarian_static_routes_are_not_shadowed_by_catchalls(): void
    {
        $this->assertSame('events.today', $this->routeNameFor('GET', '/events/today'));
        $this->assertSame('events.upcoming', $this->routeNameFor('GET', '/events/upcoming'));
        $this->assertSame('events.stats', $this->routeNameFor('GET', '/events/stats'));
        $this->assertSame('librarian.books.search', $this->routeNameFor('GET', '/librarian/books/search'));

        $this->assertRouteUsesController('events.today', EventController::class, 'getTodayEvents');
        $this->assertRouteUsesController('events.stats', EventController::class, 'getEventStats');
    }

    private function assertRouteHasMiddleware(string $routeName, string $middleware): void
    {
        $this->assertContains($middleware, $this->routeMiddleware($routeName));
    }

    private function assertRouteDoesNotHaveMiddleware(string $routeName, string $middleware): void
    {
        $this->assertNotContains($middleware, $this->routeMiddleware($routeName));
    }

    private function assertRouteUsesController(string $routeName, string $controller, string $method): void
    {
        $this->assertSame($controller.'@'.$method, $this->route($routeName)->getActionName());
    }

    private function routeMiddleware(string $routeName): array
    {
        return $this->route($routeName)->middleware();
    }

    private function routeParameterNames(string $routeName): array
    {
        return $this->route($routeName)->parameterNames();
    }

    private function routeNameFor(string $method, string $uri): string
    {
        return $this->app['router']
            ->getRoutes()
            ->match(Request::create($uri, $method))
            ->getName();
    }

    private function route(string $routeName): \Illuminate\Routing\Route
    {
        $route = $this->app['router']->getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Route [{$routeName}] is not registered.");

        return $route;
    }
}
