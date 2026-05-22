<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class AcademicAuthorizationTest extends TestCase
{
    public function test_students_cannot_post_mark_updates(): void
    {
        $student = User::factory()->make([
            'id' => 1001,
            'user_type' => 'student',
        ]);

        $response = $this->actingAs($student)->post(route('marks.update', [1, 1, 1, 1]), []);

        $response->assertRedirect(route('login'));
    }

    public function test_students_cannot_run_mark_batch_updates(): void
    {
        $student = User::factory()->make([
            'id' => 1002,
            'user_type' => 'student',
        ]);

        $response = $this->actingAs($student)->post(route('marks.batch_update'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_teachers_cannot_mutate_timetables(): void
    {
        $teacher = User::factory()->make([
            'id' => 1003,
            'user_type' => 'teacher',
        ]);

        $response = $this->actingAs($teacher)->post(route('ttr.bulk_store_subjects', 1), [
            'assignments' => [],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_marks_year_selector_route_is_not_shadowed_by_show_route(): void
    {
        $request = Request::create('/marks/year-selector/student-hash', 'GET');
        $route = app('router')->getRoutes()->match($request);

        $this->assertSame('marks.year_selector', $route->getName());
    }
}
