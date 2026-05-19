<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibrarianBookReturnTest extends TestCase
{
    use RefreshDatabase;

    public function testReturningTheSameTransactionTwiceDoesNotInflateAvailableCopies()
    {
        $librarian = User::create([
            'name' => 'Library Staff',
            'code' => 'LIB001',
            'username' => 'librarian',
            'user_type' => 'librarian',
            'password' => bcrypt('password'),
        ]);

        $student = User::create([
            'name' => 'Student One',
            'code' => 'STU001',
            'username' => 'student',
            'user_type' => 'student',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'isbn' => '9780000000001',
            'name' => 'Critical Systems',
            'title' => 'Critical Systems',
            'author' => 'Test Author',
            'category' => 'Reference',
            'copies' => 1,
            'available_copies' => 0,
            'status' => 'checked_out',
        ]);

        $transaction = BookTransaction::create([
            'book_id' => $book->id,
            'student_id' => $student->id,
            'issued_by' => $librarian->id,
            'issue_date' => now()->subDay(),
            'due_date' => now()->addDay(),
            'status' => 'issued',
        ]);

        $route = route('librarian.books.return', [
            'book' => $book->id,
            'transaction' => $transaction->id,
        ]);

        $this->actingAs($librarian)
            ->from('/librarian/transactions')
            ->post($route)
            ->assertRedirect('/librarian/transactions')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'available_copies' => 1,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('book_transactions', [
            'id' => $transaction->id,
            'status' => 'returned',
        ]);

        $this->actingAs($librarian)
            ->from('/librarian/transactions')
            ->post($route)
            ->assertRedirect('/librarian/transactions')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'available_copies' => 1,
            'status' => 'available',
        ]);
    }
}
