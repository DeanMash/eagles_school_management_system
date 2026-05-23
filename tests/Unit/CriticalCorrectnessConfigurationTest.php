<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CriticalCorrectnessConfigurationTest extends TestCase
{
    public function testAcademicMutationControllersRequireStaffMiddleware()
    {
        $routes = $this->readFile('routes/web.php');
        $markController = $this->readFile('app/Http/Controllers/SupportTeam/MarkController.php');
        $timeTableController = $this->readFile('app/Http/Controllers/SupportTeam/TimeTableController.php');

        $this->assertStringContainsString("Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware('auth')->name('dashboard')", $routes);
        $this->assertStringContainsString("\$this->middleware('teamSAT'", $markController);
        $this->assertStringContainsString("\$this->middleware('teamSA');", $timeTableController);
    }

    public function testStudentPromotionRoutesUsePromotionControllerBeforeStudentCatchAll()
    {
        $routes = $this->readFile('routes/web.php');

        $promotionRoutePos = strpos($routes, "PromotionController::class, 'promotion'");
        $studentCatchAllPos = strpos($routes, "Route::get('/{sr_id}', [StudentRecordController::class, 'show']");

        $this->assertNotFalse($promotionRoutePos);
        $this->assertNotFalse($studentCatchAllPos);
        $this->assertLessThan($studentCatchAllPos, $promotionRoutePos);
        $this->assertStringContainsString("Route::post('/promotion/selector', [PromotionController::class, 'selector'])->name('promote_selector')", $routes);
        $this->assertStringNotContainsString("StudentRecordController::class, 'promotion'", $routes);
        $this->assertStringContainsString("Route::get('/{year}', [PaymentController::class, 'show'])->name('show')", $routes);
    }

    public function testPromotionDoesNotLeakGraduationStateBetweenStudents()
    {
        $controller = $this->readFile('app/Http/Controllers/SupportTeam/PromotionController.php');

        $this->assertStringContainsString("\$d = ['grad' => 0, 'grad_date' => NULL];", $controller);
        $this->assertStringContainsString("if(!in_array(\$p, ['P', 'D', 'G']))", $controller);
    }

    public function testPaymentAndLibraryUpdatesRejectCorruptingInputs()
    {
        $paymentController = $this->readFile('app/Http/Controllers/SupportTeam/PaymentController.php');
        $bookController = $this->readFile('app/Http/Controllers/Librarian/BookController.php');

        $this->assertStringContainsString("'amt_paid' => 'required|numeric|min:0.01|max:'.\$remaining", $paymentController);
        $this->assertStringContainsString("if(\$transaction->status !== 'issued')", $bookController);
        $this->assertStringContainsString("if(\$book->available_copies < \$book->copies)", $bookController);
    }

    public function testMarksBatchUpdateStoresGradeIdNotModel()
    {
        $markController = $this->readFile('app/Http/Controllers/SupportTeam/MarkController.php');

        $this->assertStringContainsString("\$grade = \$this->mark->getGrade(\$total, \$class_type->id);", $markController);
        $this->assertStringContainsString("\$d['grade_id'] = \$grade ? \$grade->id : NULL;", $markController);
    }

    private function readFile($path)
    {
        return file_get_contents(__DIR__.'/../../'.$path);
    }
}
