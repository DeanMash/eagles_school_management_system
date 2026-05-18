<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PaymentRouteBindingTest extends TestCase
{
    public function testPaymentYearRouteDoesNotUseGlobalIdBinding(): void
    {
        $routes = file_get_contents($this->workspacePath('routes/web.php'));

        $this->assertStringContainsString(
            "Route::get('/{year}', [PaymentController::class, 'show'])->name('show');",
            $routes
        );
        $this->assertStringNotContainsString(
            "Route::get('/{id}', [PaymentController::class, 'show'])->name('show');",
            $routes
        );
    }

    public function testPaymentManagementLinksPassHashedPaymentIds(): void
    {
        $indexView = file_get_contents($this->workspacePath('resources/views/pages/support_team/payments/index.blade.php'));
        $editView = file_get_contents($this->workspacePath('resources/views/pages/support_team/payments/edit.blade.php'));

        $this->assertSame(2, substr_count($indexView, "route('payments.edit', Qs::hash(\$p->id))"));
        $this->assertSame(2, substr_count($indexView, "route('payments.destroy', Qs::hash(\$p->id))"));
        $this->assertStringContainsString(
            "route('payments.update', Qs::hash(\$payment->id))",
            $editView
        );

        $this->assertStringNotContainsString("route('payments.edit', \$p->id)", $indexView);
        $this->assertStringNotContainsString("route('payments.destroy', \$p->id)", $indexView);
        $this->assertStringNotContainsString("route('payments.update', \$payment->id)", $editView);
    }

    private function workspacePath(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$path;
    }
}
