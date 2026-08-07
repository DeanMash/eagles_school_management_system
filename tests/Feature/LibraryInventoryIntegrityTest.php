<?php

namespace Tests\Feature;

use App\Http\Controllers\Librarian\BookController;
use App\Http\Controllers\Librarian\TransactionController;
use App\Models\Book;
use Tests\TestCase;

/**
 * Library inventory integrity: book update must derive available_copies from
 * outstanding loans (not request input) and sync status; markAsLost must
 * permanently reduce copies and reject non-issued transactions.
 */
class LibraryInventoryIntegrityTest extends TestCase
{
    public function test_book_update_derives_available_copies_and_rejects_below_outstanding()
    {
        $update = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/Librarian/BookController.php')),
            'update'
        );

        $this->assertStringContainsString("where('status', 'issued')", $update);
        $this->assertStringContainsString('$copies < $outstanding', $update);
        $this->assertStringContainsString("\$data['available_copies'] = \$copies - \$outstanding", $update);
        $this->assertStringContainsString('$request->only(', $update);

        // Must not mass-assign raw request (forged available_copies / status tricks)
        $this->assertStringNotContainsString('$request->all()', $update);
        $this->assertStringNotContainsString('$book->available_copies + $difference', $update);
    }

    public function test_book_update_syncs_checked_out_status_when_copies_become_available()
    {
        $update = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/Librarian/BookController.php')),
            'update'
        );

        $this->assertStringContainsString("\$data['available_copies'] > 0 ? 'available' : 'checked_out'", $update);
        $this->assertStringContainsString("'available', 'checked_out'", $update);
        $this->assertTrue(method_exists(BookController::class, 'update'));
        $this->assertTrue(method_exists(Book::class, 'isAvailable'));
    }

    public function test_book_store_does_not_accept_forged_available_copies()
    {
        $store = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/Librarian/BookController.php')),
            'store'
        );

        $this->assertStringContainsString('$request->only(', $store);
        $this->assertStringContainsString("\$data['available_copies'] = \$copies", $store);
        $this->assertStringNotContainsString('$request->all()', $store);
    }

    public function test_mark_as_lost_reduces_copies_only_for_issued_transactions()
    {
        $markAsLost = $this->extractMethod(
            file_get_contents(app_path('Http/Controllers/Librarian/TransactionController.php')),
            'markAsLost'
        );

        $this->assertStringContainsString('DB::transaction', $markAsLost);
        $this->assertStringContainsString("status !== 'issued'", $markAsLost);
        $this->assertStringContainsString('lockForUpdate()', $markAsLost);
        $this->assertStringContainsString('max(0, $book->copies - 1)', $markAsLost);
        $this->assertTrue(method_exists(TransactionController::class, 'markAsLost'));
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
