<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CriticalRouteRegressionTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return file_get_contents(dirname(__DIR__, 2).'/'.$path);
    }

    public function test_helper_autoload_path_matches_linux_case(): void
    {
        $composer = $this->projectFile('composer.json');

        $this->assertStringContainsString('"app/Helpers/helpers.php"', $composer);
        $this->assertStringNotContainsString('"app/helpers/helpers.php"', $composer);
    }

    public function test_promotion_routes_use_promotion_controller_before_student_wildcard(): void
    {
        $routes = $this->projectFile('routes/web.php');

        $promotionRoute = strpos($routes, "Route::get('/promotion/manage', [PromotionController::class, 'manage'])");
        $studentWildcard = strpos($routes, "Route::get('/{id}', [StudentRecordController::class, 'show'])");

        $this->assertNotFalse($promotionRoute);
        $this->assertNotFalse($studentWildcard);
        $this->assertLessThan($studentWildcard, $promotionRoute);
        $this->assertStringNotContainsString("StudentRecordController::class, 'promotion'", $routes);
        $this->assertStringNotContainsString("StudentRecordController::class, 'promote'", $routes);
        $this->assertStringContainsString("Route::delete('/promotion/reset/{promotion_id}', [PromotionController::class, 'reset'])", $routes);
    }

    public function test_academic_write_routes_require_role_middleware(): void
    {
        $routes = $this->projectFile('routes/web.php');

        $this->assertStringContainsString("Route::middleware(['auth', 'teamSA'])->group(function ()", $routes);
        $this->assertStringContainsString("Route::middleware(['teamSAT'])->group(function ()", $routes);
        $this->assertStringContainsString("Route::put('/batch-update'", $routes);
        $this->assertStringContainsString("Route::put('/update/{exam_id}/{class_id}/{section_id}/{subject_id}'", $routes);
        $this->assertStringNotContainsString("Route::post('/batch-update'", $routes);
        $this->assertStringNotContainsString("Route::post('/update/{exam_id}/{class_id}/{section_id}/{subject_id}'", $routes);
    }

    public function test_static_mark_routes_are_before_marksheet_wildcard(): void
    {
        $routes = $this->projectFile('routes/web.php');

        $yearSelector = strpos($routes, "Route::get('/year-selector/{student_id}'");
        $print = strpos($routes, "Route::get('/print/{student_id}/{exam_id}/{year}'");
        $wildcard = strpos($routes, "Route::get('/{student_id}/{year}'");

        $this->assertNotFalse($yearSelector);
        $this->assertNotFalse($print);
        $this->assertNotFalse($wildcard);
        $this->assertLessThan($wildcard, $yearSelector);
        $this->assertLessThan($wildcard, $print);
    }

    public function test_mark_controller_decodes_marksheet_ids_and_keeps_write_guard(): void
    {
        $controller = $this->projectFile('app/Http/Controllers/SupportTeam/MarkController.php');

        $this->assertStringContainsString("\$this->middleware('teamSAT'", $controller);
        $this->assertStringContainsString('$student_id = $this->decodeStudentId($student_id);', $controller);
        $this->assertStringContainsString('$d[\'grade_id\'] = $grade ? $grade->id : NULL;', $controller);
    }

    public function test_student_controller_does_not_double_decode_bound_id_routes(): void
    {
        $controller = $this->projectFile('app/Http/Controllers/SupportTeam/StudentRecordController.php');

        $this->assertStringNotContainsString('Qs::decodeHash($sr_id)', $controller);
        $this->assertStringNotContainsString('Qs::decodeHash($st_id)', $controller);
        $this->assertStringContainsString("'not_graduated'", $controller);
    }
}
