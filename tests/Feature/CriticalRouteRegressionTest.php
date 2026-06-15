<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CriticalRouteRegressionTest extends TestCase
{
    public function test_static_routes_are_not_shadowed_by_wildcards()
    {
        $this->assertRouteMatches('GET', '/students/promotion/manage', 'students.promotion_manage');
        $this->assertRouteMatches('GET', '/students/graduated/list', 'students.graduated');
        $this->assertRouteMatches('GET', '/marks/year-selector/student-hash', 'marks.year_selector');
        $this->assertRouteMatches('GET', '/marks/print/student-hash/1/2024-2025', 'marks.print');
        $this->assertRouteMatches('GET', '/librarian/books/search', 'librarian.books.search');
        $this->assertRouteMatches('GET', '/events/today', 'events.today');
        $this->assertRouteMatches('GET', '/events/upcoming', 'events.upcoming');
        $this->assertRouteMatches('GET', '/events/stats', 'events.stats');
        $this->assertRouteMatches('GET', '/pins/enter/student-hash', 'pins.enter');
    }

    public function test_payment_year_and_numeric_ids_are_not_hash_decoded()
    {
        $this->assertSame('payments/{year}', Route::getRoutes()->getByName('payments.show')->uri());
        $this->assertSame('payments/{payment_id}/edit', Route::getRoutes()->getByName('payments.edit')->uri());
        $this->assertSame('payments/{payment_id}', Route::getRoutes()->getByName('payments.update')->uri());
        $this->assertSame('payments/{payment_id}', Route::getRoutes()->getByName('payments.destroy')->uri());

        $this->assertRouteMatches('GET', '/payments/2024-2025', 'payments.show');
        $this->assertRouteMatches('GET', '/payments/17/edit', 'payments.edit');
    }

    public function test_critical_academic_write_routes_are_role_guarded()
    {
        $this->assertContains('teamSA', Route::getRoutes()->getByName('tt.store')->gatherMiddleware());
        $this->assertContains('teamSA', Route::getRoutes()->getByName('ttr.destroy')->gatherMiddleware());
        $this->assertSame('timetables/{tt_id}', Route::getRoutes()->getByName('tt.update')->uri());
        $this->assertSame('timetables/time-slots/{ts_id}', Route::getRoutes()->getByName('tt.time_slots.update')->uri());
        $this->assertSame('classes/{class_id}/edit', Route::getRoutes()->getByName('classes.edit')->uri());
        $this->assertSame('events/{event}', Route::getRoutes()->getByName('events.show')->uri());

        $this->assertContains('PUT', Route::getRoutes()->getByName('marks.update')->methods());
        $this->assertContains('PUT', Route::getRoutes()->getByName('marks.batch_update')->methods());
        $this->assertTrue(Route::has('marks.comment_update'));
        $this->assertTrue(Route::has('marks.skills_update'));
        $this->assertTrue(Route::has('marks.print_tabulation'));

        $source = file_get_contents(app_path('Http/Controllers/SupportTeam/MarkController.php'));
        $this->assertStringContainsString('$this->middleware(\'teamSAT\'', $source);
    }

    public function test_librarian_returns_are_idempotent_and_inventory_capped()
    {
        $this->assertSame(
            'librarian/books/{book}/return/{transaction}',
            Route::getRoutes()->getByName('librarian.books.return')->uri()
        );

        $source = file_get_contents(app_path('Http/Controllers/Librarian/BookController.php'));

        $this->assertStringContainsString('DB::transaction', $source);
        $this->assertStringContainsString('$lockedTransaction->status !== \'issued\'', $source);
        $this->assertStringContainsString('min($lockedBook->copies', $source);
    }

    public function test_composer_autoloads_helper_file_with_case_sensitive_path()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $this->assertContains('app/Helpers/helpers.php', $composer['autoload']['files']);
    }

    private function assertRouteMatches($method, $uri, $expectedName)
    {
        $request = Request::create($uri, $method);
        $route = Route::getRoutes()->match($request);

        $this->assertSame($expectedName, $route->getName());
    }
}
