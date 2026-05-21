<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarksRoutingAuthorizationTest extends TestCase
{
    public function test_marks_controller_protects_management_actions_with_team_sat_middleware()
    {
        $controller = file_get_contents(app_path('Http/Controllers/SupportTeam/MarkController.php'));

        $this->assertStringContainsString(
            "\$this->middleware('teamSAT', ['except' => ['show', 'year_selected', 'year_selector', 'print_view']]);",
            $controller
        );
        $this->assertStringNotContainsString(
            "//$this->middleware('teamSAT'",
            str_replace(' ', '', $controller)
        );
    }

    public function test_marks_specific_routes_are_registered_before_student_marksheet_catch_all()
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $showRoute = "Route::get('/{student_id}/{year}'";

        foreach ([
            "Route::get('/year-selector/{student_id}'",
            "Route::post('/year-selected/{student_id}'",
            "Route::get('/print/{student_id}/{exam_id}/{year}'",
            "Route::post('/comment/{exr_id}'",
            "Route::post('/skills/{skill}/{exr_id}'",
            "Route::get('/tabulation/print/{exam_id}/{class_id}/{section_id}'",
        ] as $route) {
            $this->assertLessThan(
                strpos($routes, $showRoute),
                strpos($routes, $route),
                "{$route} must be registered before the marksheet catch-all route."
            );
        }

        $this->assertStringContainsString("->name('print')", $routes);
        $this->assertStringContainsString("->name('comment_update')", $routes);
        $this->assertStringContainsString("->name('skills_update')", $routes);
        $this->assertStringContainsString("->name('print_tabulation')", $routes);
    }
}
