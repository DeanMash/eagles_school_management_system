<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // Display all books
    public function index()
    {
        $books = Book::withCount(['transactions' => function($query) {
            $query->where('status', 'issued');
        }])->latest()->paginate(20);
        
        return view('pages.librarian.books.index', compact('books'));
    }

    // Show create book form
    public function create()
    {
        $categories = [
            'Fiction', 'Non-Fiction', 'Science', 'Mathematics', 
            'History', 'Geography', 'Literature', 'Reference',
            'Biography', 'Art', 'Technology', 'Other'
        ];
        
        return view('pages.librarian.books.create', compact('categories'));
    }

    // Store new book
    public function store(Request $request)
    {
        $request->validate([
            'isbn' => 'nullable|unique:books|max:20',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'year_published' => 'nullable|integer|min:1000|max:' . date('Y'),
            'category' => 'required|string|max:100',
            'copies' => 'required|integer|min:1|max:1000',
            'shelf_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'book_cover' => 'nullable|image|max:2048'
        ]);

        $data = $request->all();
        $data['available_copies'] = $request->copies;
        $data['name'] = $request->title; // Set name from title

        // Handle book cover upload
        if ($request->hasFile('book_cover')) {
            $path = $request->file('book_cover')->store('book_covers', 'public');
            $data['book_cover'] = $path;
        }

        Book::create($data);

        return redirect()->route('librarian.books.index')
            ->with('success', 'Book added successfully!');
    }

    // Show edit book form
    public function edit(Book $book)
    {
        $categories = [
            'Fiction', 'Non-Fiction', 'Science', 'Mathematics', 
            'History', 'Geography', 'Literature', 'Reference',
            'Biography', 'Art', 'Technology', 'Other'
        ];
        
        return view('pages.librarian.books.edit', compact('book', 'categories'));
    }

    // Update book
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'isbn' => 'nullable|unique:books,isbn,' . $book->id . '|max:20',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'year_published' => 'nullable|integer|min:1000|max:' . date('Y'),
            'category' => 'required|string|max:100',
            'copies' => 'required|integer|min:1|max:1000',
            'shelf_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'book_cover' => 'nullable|image|max:2048'
        ]);

        $data = $request->all();
        $data['name'] = $request->title; // Set name from title
        
        // Update available copies if total copies changed
        if ($request->copies != $book->copies) {
            $difference = $request->copies - $book->copies;
            $data['available_copies'] = max(0, $book->available_copies + $difference);
        }

        // Handle book cover upload
        if ($request->hasFile('book_cover')) {
            // Delete old cover if exists
            if ($book->book_cover && Storage::disk('public')->exists($book->book_cover)) {
                Storage::disk('public')->delete($book->book_cover);
            }
            
            $path = $request->file('book_cover')->store('book_covers', 'public');
            $data['book_cover'] = $path;
        }

        $book->update($data);

        return redirect()->route('librarian.books.index')
            ->with('success', 'Book updated successfully!');
    }

    // Delete book
    public function destroy(Book $book)
    {
        // Check if book has active transactions
        if ($book->transactions()->where('status', 'issued')->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete book with active transactions!');
        }

        // Delete book cover if exists
        if ($book->book_cover && Storage::disk('public')->exists($book->book_cover)) {
            Storage::disk('public')->delete($book->book_cover);
        }

        $book->delete();

        return redirect()->route('librarian.books.index')
            ->with('success', 'Book deleted successfully!');
    }

    // Show book details
    public function show(Book $book)
    {
        $transactions = $book->transactions()->with(['student', 'issuer'])->latest()->paginate(10);
        return view('pages.librarian.books.show', compact('book', 'transactions'));
    }

    // Issue book to student
    public function issueForm(Book $book)
    {
        if (!$book->isAvailable()) {
            return redirect()->route('librarian.books.index')
                ->with('error', 'Book is not available for issuing!');
        }
        
        $students = User::where('user_type', 'student')
            ->whereHas('studentRecord')
            ->orderBy('name')
            ->get();
        
        return view('pages.librarian.books.issue', compact('book', 'students'));
    }

    public function issueBook(Request $request, Book $book)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after:today'
        ]);

        if (!$book->isAvailable()) {
            return redirect()->back()
                ->with('error', 'Book is not available for issue!');
        }

        BookTransaction::create([
            'book_id' => $book->id,
            'student_id' => $request->student_id,
            'issued_by' => Auth::id(),
            'issue_date' => now(),
            'due_date' => $request->due_date,
            'status' => 'issued',
            'notes' => $request->notes
        ]);

        // Update available copies
        $book->decrement('available_copies');
        if ($book->available_copies == 0) {
            $book->update(['status' => 'checked_out']);
        }

        return redirect()->route('librarian.books.index')
            ->with('success', 'Book issued successfully!');
    }

    // Return book
    public function returnBook(Book $book, BookTransaction $transaction)
    {
        return DB::transaction(function () use ($book, $transaction) {
            $transaction = BookTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($transaction->book_id !== $book->id) {
                abort(404);
            }

            if ($transaction->status !== 'issued') {
                return redirect()->back()
                    ->with('error', 'This book transaction has already been returned.');
            }

            $book = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();

            $transaction->update([
                'return_date' => now(),
                'status' => 'returned',
                'fine_amount' => $transaction->calculateFine(),
                'notes' => $transaction->notes . "\n\nReturned on: " . now()->format('Y-m-d')
            ]);

            if ($book->available_copies < $book->copies) {
                $book->increment('available_copies');
                $book->refresh();
            }

            if ($book->available_copies > 0 && $book->status === 'checked_out') {
                $book->update(['status' => 'available']);
            }

            return redirect()->back()
                ->with('success', 'Book returned successfully!');
        });
    }

    // Search books
    public function search(Request $request)
    {
        $query = Book::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('author', 'like', "%$search%")
                  ->orWhere('isbn', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            });
        }
        
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }
        
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        $books = $query->latest()->paginate(20);
        
        $categories = [
            'Fiction', 'Non-Fiction', 'Science', 'Mathematics', 
            'History', 'Geography', 'Literature', 'Reference',
            'Biography', 'Art', 'Technology', 'Other'
        ];
        
        return view('pages.librarian.books.index', compact('books', 'categories'));
    }
}