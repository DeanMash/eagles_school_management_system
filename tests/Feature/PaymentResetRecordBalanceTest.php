<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PaymentController;
use Tests\TestCase;

class PaymentResetRecordBalanceTest extends TestCase
{
    public function test_reset_record_restores_balance_to_invoice_amount_not_zero()
    {
        $reset = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/SupportTeam/PaymentController.php')),
            'reset_record'
        );

        // Must load the payment so outstanding balance can be restored.
        $this->assertStringContainsString('findRecord($id)', $reset);
        $this->assertStringContainsString('find($record->payment_id)', $reset);
        $this->assertStringContainsString("'balance' => \$payment->amount", $reset);

        // The old bug zeroed amt_paid, paid, AND balance in one assignment.
        $this->assertStringNotContainsString(
            "\$pr['amt_paid'] = \$pr['paid'] = \$pr['balance'] = 0",
            $reset
        );
        $this->assertDoesNotMatchRegularExpression(
            "/'balance'\s*=>\s*0\b/",
            $reset
        );
    }

    public function test_reset_record_clears_paid_state_and_receipts()
    {
        $reset = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/SupportTeam/PaymentController.php')),
            'reset_record'
        );

        $this->assertStringContainsString("'amt_paid' => 0", $reset);
        $this->assertStringContainsString("'paid' => 0", $reset);
        $this->assertStringContainsString('deleteReceipts(', $reset);
        $this->assertStringContainsString("['pr_id' => \$id]", $reset);
    }

    public function test_manage_balance_aggregation_uses_stored_balance_when_present()
    {
        // Documents why balance must not be stored as 0 after reset:
        // manage() prefers a non-null balance over recomputing from the invoice,
        // so balance=0 after wipe makes students show as "Cleared".
        $manage = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/SupportTeam/PaymentController.php')),
            'manage'
        );

        $this->assertStringContainsString('$pr->balance ??', $manage);
        $this->assertTrue(method_exists(PaymentController::class, 'reset_record'));
    }

    private function extractMethod(string $source, string $method): string
    {
        if (!preg_match(
            '/function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*\{/',
            $source,
            $match,
            PREG_OFFSET_CAPTURE
        )) {
            $this->fail("Could not find method {$method}");
        }

        $start = $match[0][1];
        $brace = strpos($source, '{', $start);
        $depth = 0;
        $len = strlen($source);

        for ($i = $brace; $i < $len; $i++) {
            $ch = $source[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }

        $this->fail("Could not extract method body for {$method}");
    }
}
