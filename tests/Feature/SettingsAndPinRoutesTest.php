<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SettingsAndPinRoutesTest extends TestCase
{
    public function test_settings_and_pin_routes_are_registered()
    {
        $expected = [
            'settings' => 'GET',
            'settings.update' => 'PUT',
            'pins.index' => 'GET',
            'pins.create' => 'GET',
            'pins.store' => 'POST',
            'pins.enter' => 'GET',
            'pins.verify' => 'POST',
            'pins.destroy' => 'DELETE',
        ];

        foreach ($expected as $name => $method) {
            $this->assertTrue(Route::has($name), "Missing named route [{$name}]");
            $route = Route::getRoutes()->getByName($name);
            $this->assertContains(
                $method,
                $route->methods(),
                "Route [{$name}] should accept {$method}"
            );
        }
    }

    public function test_settings_routes_require_super_admin_middleware()
    {
        foreach (['settings', 'settings.update'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('super_admin', $middleware, "Route [{$name}] must use super_admin middleware");
            $this->assertContains('auth', $middleware, "Route [{$name}] must use auth middleware");
        }
    }
}
