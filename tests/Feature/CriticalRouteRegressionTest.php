<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PromotionController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CriticalRouteRegressionTest extends TestCase
{
    public function test_promotion_routes_use_promotion_controller_before_student_wildcard(): void
    {
        $promotion = Route::getRoutes()->getByName('students.promotion');
        $studentShow = Route::getRoutes()->getByName('students.show');

        $this->assertNotNull($promotion);
        $this->assertNotNull($studentShow);
        $this->assertSame(PromotionController::class.'@promotion', $promotion->getActionName());

        $studentRoutes = collect(Route::getRoutes())->values()
            ->filter(function ($route) {
                return strpos($route->uri(), 'students/') === 0;
            })
            ->values();

        $promotionIndex = $studentRoutes->search(function ($route) {
            return $route->getName() === 'students.promotion';
        });
        $showIndex = $studentRoutes->search(function ($route) {
            return $route->getName() === 'students.show';
        });

        $this->assertNotFalse($promotionIndex);
        $this->assertNotFalse($showIndex);
        $this->assertLessThan($showIndex, $promotionIndex);
    }

    public function test_payment_show_route_does_not_use_global_id_binding(): void
    {
        $this->assertSame('payments/{year}', Route::getRoutes()->getByName('payments.show')->uri());
        $this->assertSame('payments/{payment_id}/edit', Route::getRoutes()->getByName('payments.edit')->uri());
    }

    public function test_marks_and_pin_routes_required_by_views_exist_before_marks_show(): void
    {
        foreach ([
            'marks.year_selector',
            'marks.year_selected',
            'marks.print',
            'marks.print_tabulation',
            'marks.comment_update',
            'marks.skills_update',
            'pins.enter',
            'pins.verify',
        ] as $routeName) {
            $this->assertNotNull(Route::getRoutes()->getByName($routeName), "Missing route [{$routeName}]");
        }

        $marksRoutes = collect(Route::getRoutes())->values()
            ->filter(function ($route) {
                return strpos($route->uri(), 'marks/') === 0;
            })
            ->values();

        $showIndex = $marksRoutes->search(function ($route) {
            return $route->getName() === 'marks.show';
        });

        foreach (['marks.year_selector', 'marks.print'] as $routeName) {
            $routeIndex = $marksRoutes->search(function ($route) use ($routeName) {
                return $route->getName() === $routeName;
            });
            $this->assertNotFalse($routeIndex);
            $this->assertLessThan($showIndex, $routeIndex);
        }
    }

    public function test_academic_write_routes_require_staff_middleware(): void
    {
        foreach (['marks.selector', 'marks.update', 'marks.batch_update'] as $routeName) {
            $this->assertContains('teamSAT', Route::getRoutes()->getByName($routeName)->gatherMiddleware());
        }

        foreach (['tt.store', 'tt.delete', 'ttr.store', 'ttr.store_weekly_slots'] as $routeName) {
            $this->assertContains('teamSA', Route::getRoutes()->getByName($routeName)->gatherMiddleware());
        }
    }
}
