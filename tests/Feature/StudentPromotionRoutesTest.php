<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PromotionController;
use App\Http\Controllers\SupportTeam\StudentRecordController;
use Tests\TestCase;

class StudentPromotionRoutesTest extends TestCase
{
    public function testPromotionRoutesResolveToPromotionController(): void
    {
        $this->assertRouteUses('students.promotion', PromotionController::class, 'promotion');
        $this->assertRouteUses('students.promote_selector', PromotionController::class, 'selector');
        $this->assertRouteUses('students.promote', PromotionController::class, 'promote');
        $this->assertRouteUses('students.promotion_manage', PromotionController::class, 'manage');
        $this->assertRouteUses('students.promotion_reset', PromotionController::class, 'reset');
        $this->assertRouteUses('students.promotion_reset_all', PromotionController::class, 'reset_all');
    }

    public function testPromotionRoutesGenerateExpectedUrls(): void
    {
        $this->assertSame('http://localhost/students/promotion', route('students.promotion'));
        $this->assertSame('http://localhost/students/promotion/1/2/3/4', route('students.promotion', [1, 2, 3, 4]));
        $this->assertSame('http://localhost/students/promotion/selector', route('students.promote_selector'));
        $this->assertSame('http://localhost/students/promotion/1/2/3/4', route('students.promote', [1, 2, 3, 4]));
        $this->assertSame('http://localhost/students/promotion/manage', route('students.promotion_manage'));
        $this->assertSame('http://localhost/students/promotion/reset/9', route('students.promotion_reset', 9));
        $this->assertSame('http://localhost/students/promotion/reset-all', route('students.promotion_reset_all'));
    }

    public function testStudentShowRouteStillUsesStudentController(): void
    {
        $this->assertRouteUses('students.show', StudentRecordController::class, 'show');
    }

    private function assertRouteUses(string $name, string $controller, string $method): void
    {
        $route = app('router')->getRoutes()->getByName($name);

        $this->assertNotNull($route, "Route [{$name}] was not registered.");
        $this->assertSame($controller.'@'.$method, $route->getActionName());
    }
}
