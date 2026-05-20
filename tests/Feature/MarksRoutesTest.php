<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\MarkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MarksRoutesTest extends TestCase
{
    public function testYearSelectorRouteIsRegisteredBeforeMarksheetCatchAll()
    {
        $route = Route::getRoutes()->match(Request::create('/marks/year-selector/1', 'GET'));

        $this->assertSame('marks.year_selector', $route->getName());
        $this->assertSame(MarkController::class.'@year_selector', $route->getActionName());

        $route = Route::getRoutes()->match(Request::create('/marks/year-selected/1', 'POST'));

        $this->assertSame('marks.year_selected', $route->getName());
        $this->assertSame(MarkController::class.'@year_selected', $route->getActionName());
    }
}
