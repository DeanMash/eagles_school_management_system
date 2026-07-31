<?php

namespace Tests\Feature;

use App\Http\Controllers\SupportTeam\PaymentController;
use App\Repositories\PaymentRepo;
use Tests\TestCase;

class PaymentPayNowConcurrencyTest extends TestCase
{
    public function test_pay_now_wraps_update_and_receipt_in_a_transaction()
    {
        $source = file_get_contents(app_path('Http/Controllers/SupportTeam/PaymentController.php'));

        $this->assertStringContainsString('use Illuminate\\Support\\Facades\\DB;', $source);
        $this->assertMatchesRegularExpression(
            '/function\s+pay_now\s*\([^)]*\)\s*\{[\s\S]*?return\s+DB::transaction\s*\(/',
            $source
        );
        $this->assertStringContainsString('createReceipt(', $source);
        $this->assertStringContainsString('updateRecord(', $source);
    }

    public function test_pay_now_locks_payment_record_before_read_modify_write()
    {
        $controller = file_get_contents(app_path('Http/Controllers/SupportTeam/PaymentController.php'));
        $repo = file_get_contents(app_path('Repositories/PaymentRepo.php'));

        $this->assertStringContainsString('findRecordForUpdate(', $controller);
        $this->assertMatchesRegularExpression(
            '/function\s+findRecordForUpdate\s*\(\s*\$id\s*\)\s*\{[\s\S]*?lockForUpdate\s*\(/',
            $repo
        );

        // Read-modify-write must happen after the locked fetch, not via unlocked findRecord.
        $payNow = $this->extractMethod($controller, 'pay_now');
        $this->assertStringContainsString('findRecordForUpdate($pr_id)', $payNow);
        $this->assertStringNotContainsString('findRecord($pr_id)', $payNow);
    }

    public function test_pay_now_rejects_non_positive_amounts()
    {
        $payNow = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/SupportTeam/PaymentController.php')),
            'pay_now'
        );

        $this->assertMatchesRegularExpression(
            "/'amt_paid'\s*=>\s*'required\\|numeric\\|gt:0'/",
            $payNow
        );
    }

    public function test_payment_repo_exposes_locked_record_lookup()
    {
        $this->assertTrue(method_exists(PaymentRepo::class, 'findRecordForUpdate'));
        $this->assertTrue(method_exists(PaymentController::class, 'pay_now'));
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
