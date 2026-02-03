<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookTransaction;
use App\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $user;

    public function __construct(UserRepo $user)
    {
        $this->middleware('librarian');
        $this->user = $user;
    }

    public function index()
    {
        $totalBooks = Book::count();
        $availableBooks = Book::where('status', 'available')->count();
        $issuedBooks = BookTransaction::where('status', 'issued')->count();
        $overdueBooks = BookTransaction::where('status', 'issued')
            ->where('due_date', '<', now())
            ->count();
        
        $recentTransactions = BookTransaction::with(['book', 'student', 'issuer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $overdueTransactions = BookTransaction::with(['book', 'student', 'issuer'])
            ->where('status', 'issued')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->get();

        $data = [
            'totalBooks' => $totalBooks,
            'availableBooks' => $availableBooks,
            'issuedBooks' => $issuedBooks,
            'overdueBooks' => $overdueBooks,
            'recentTransactions' => $recentTransactions,
            'overdueTransactions' => $overdueTransactions,
        ];

        return view('pages.librarian.dashboard', $data);
    }
}
