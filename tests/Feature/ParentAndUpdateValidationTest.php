<?php

namespace Tests\Feature;

use App\Http\Controllers\MyParent\MyController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ParentAndUpdateValidationTest extends TestCase
{
    public function test_my_children_route_uses_parent_controller_and_view()
    {
        $this->assertTrue(Route::has('my_children'), 'Missing named route [my_children]');

        $route = Route::getRoutes()->getByName('my_children');
        $this->assertSame('my-children', $route->uri());
        $this->assertContains('GET', $route->methods());
        $this->assertSame(MyController::class . '@children', $route->getActionName());

        $middleware = $route->gatherMiddleware();
        $this->assertContains('auth', $middleware);
        $this->assertContains('parent', $middleware);

        $this->assertFileExists(resource_path('views/pages/parent/children.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/parents/my-children.blade.php'));
    }

    public function test_student_update_request_uses_bound_route_id()
    {
        $source = file_get_contents(app_path('Http/Requests/Student/StudentRecordUpdateRequest.php'));

        $this->assertStringContainsString("route('id')", $source);
        $this->assertStringNotContainsString("route('sr_id')", $source);
        $this->assertStringNotContainsString('decodeHash($student)', $source);
    }

    public function test_user_update_request_ignores_current_user_email_by_route_id()
    {
        $source = file_get_contents(app_path('Http/Requests/UserRequest.php'));

        $this->assertStringContainsString("route('id')", $source);
        $this->assertStringContainsString("unique:users,email,'.\$userId", $source);
        $this->assertStringNotContainsString('unique:users,email,\'.$this->user', $source);
        $this->assertStringNotContainsString('Qs::decodeHash($this->user)', $source);
    }
}
