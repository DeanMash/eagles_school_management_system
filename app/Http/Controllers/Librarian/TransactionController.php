<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\BookTransaction;
use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Display all transactions
    public function index()
    {
        $transactions = BookTransaction::with(['book', 'student', 'issuer'])
            ->latest()
            ->paginate(20);
        
        $stats = [
            'total' => BookTransaction::count(),
            'issued' => BookTransaction::where('status', 'issued')->count(),
            'overdue' => BookTransaction::where('status', 'issued')
                ->where('due_date', '<', now())
                ->count(),
            'returned' => BookTransaction::where('status', 'returned')->count(),
        ];
        
        return view('pages.librarian.transactions.index', compact('transactions', 'stats'));
    }

    // Show overdue books
    public function overdue()
    {
        $transactions = BookTransaction::with(['book', 'student', 'issuer'])
            ->where('status', 'issued')
            ->where('due_date', '<', now())
            ->latest()
            ->get();
        
        $totalFines = $transactions->sum(function($transaction) {
            return $transaction->calculateFine();
        });
        
        return view('pages.librarian.transactions.overdue', compact('transactions', 'totalFines'));
    }

    // Search transactions
    public function search(Request $request)
    {
        $query = BookTransaction::with(['book', 'student', 'issuer']);
        
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }
        
        if ($request->has('from_date')) {
            $query->whereDate('issue_date', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('issue_date', '<=', $request->to_date);
        }
        
        $transactions = $query->latest()->paginate(20);
        
        $stats = [
            'total' => BookTransaction::count(),
            'issued' => BookTransaction::where('status', 'issued')->count(),
            'overdue' => BookTransaction::where('status', 'issued')
                ->where('due_date', '<', now())
                ->count(),
            'returned' => BookTransaction::where('status', 'returned')->count(),
        ];
        
        return view('pages.librarian.transactions.index', compact('transactions', 'stats'));
    }

    // Generate report
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        
        $transactions = BookTransaction::with(['book', 'student', 'issuer'])
            ->whereBetween('issue_date', [$request->start_date, $request->end_date])
            ->get();
        
        $reportData = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_issued' => $transactions->where('status', 'issued')->count(),
            'total_returned' => $transactions->where('status', 'returned')->count(),
            'total_fines' => $transactions->sum('fine_amount'),
            'most_popular_book' => Book::find(
                $transactions->groupBy('book_id')
                    ->map->count()
                    ->sortDesc()
                    ->keys()
                    ->first()
            ),
            'transactions' => $transactions
        ];
        
        return view('pages.librarian.transactions.report', compact('reportData'));
    }

    // Mark as lost
    public function markAsLost(BookTransaction $transaction)
    {
        return DB::transaction(function () use ($transaction) {
            $transaction = BookTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            // Only an issued loan can become lost; otherwise copies would be
            // decremented again and permanently erase inventory.
            if ($transaction->status !== 'issued') {
                return redirect()->back()
                    ->with('error', 'Only issued books can be marked as lost.');
            }

            $book = Book::whereKey($transaction->book_id)->lockForUpdate()->firstOrFail();

            $transaction->update([
                'status' => 'lost',
                'fine_amount' => 500, // Fixed lost book fine
                'notes' => $transaction->notes . "\n\nMarked as lost on: " . now()->format('Y-m-d')
            ]);

            // The issued copy was already removed from available_copies at issue
            // time. Permanently reduce total copies so stock cannot be re-issued.
            $copies = max(0, $book->copies - 1);
            $available = min($book->available_copies, $copies);
            $bookData = [
                'copies' => $copies,
                'available_copies' => $available,
            ];
            if ($available > 0 && $book->status === 'checked_out') {
                $bookData['status'] = 'available';
            } elseif ($available === 0 && $book->status === 'available') {
                $bookData['status'] = 'checked_out';
            }
            $book->update($bookData);

            return redirect()->back()
                ->with('success', 'Book marked as lost. Fine of $500 applied.');
        });
    }

    // Pay fine
    public function payFine(BookTransaction $transaction)
    {
        $transaction->update([
            'fine_amount' => 0,
            'notes' => $transaction->notes . "\n\nFine paid on: " . now()->format('Y-m-d')
        ]);
        
        return redirect()->back()
            ->with('success', 'Fine paid successfully.');
    }
}