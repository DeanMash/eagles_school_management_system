<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PromotionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StudentPromotionRoutesTest extends TestCase
{
    public function testPromotionRoutesAreRegisteredBeforeStudentCatchAll()
    {
        $this->assertRoute('/students/promotion', 'GET', 'students.promotion', PromotionController::class.'@promotion');
        $this->assertRoute('/students/promotion/1/2/3/4', 'GET', 'students.promotion', PromotionController::class.'@promotion');
        $this->assertRoute('/students/promotion/manage', 'GET', 'students.promotion_manage', PromotionController::class.'@manage');
        $this->assertRoute('/students/promotion/selector', 'POST', 'students.promote_selector', PromotionController::class.'@selector');
        $this->assertRoute('/students/promotion/1/2/3/4', 'POST', 'students.promote', PromotionController::class.'@promote');
        $this->assertRoute('/students/promotion/reset/1', 'DELETE', 'students.promotion_reset', PromotionController::class.'@reset');
        $this->assertRoute('/students/promotion/reset-all', 'DELETE', 'students.promotion_reset_all', PromotionController::class.'@reset_all');
        $this->assertRoute('/students/class/1', 'GET', 'students.list_by_class');
        $this->assertRoute('/students/get-sections/1', 'GET', 'students.get_sections');
    }

    private function assertRoute($uri, $method, $name, $action = null)
    {
        $route = Route::getRoutes()->match(Request::create($uri, $method));

        $this->assertSame($name, $route->getName());

        if ($action) {
            $this->assertSame($action, $route->getActionName());
        }
    }
}
