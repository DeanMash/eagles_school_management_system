@extends('layouts.master')
@section('page_title', 'Book Transactions')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Book Transactions</h6>
        <div class="header-elements">
            <a href="{{ route('librarian.transactions.overdue') }}" class="btn btn-danger">
                <i class="icon-warning"></i> View Overdue Books
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        <small>Total Transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $stats['issued'] ?? 0 }}</h3>
                        <small>Currently Issued</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $stats['overdue'] ?? 0 }}</h3>
                        <small>Overdue</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $stats['returned'] ?? 0 }}</h3>
                        <small>Returned</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('librarian.transactions.search') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="all">All Status</option>
                        <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="icon-search4"></i> Search
                    </button>
                </div>
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('error') }}
            </div>
        @endif

        <!-- Transactions Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Book</th>
                        <th>Student</th>
                        <th>Issued By</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                            <td>
                                <strong>{{ $transaction->book->title ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $transaction->book->author ?? '' }}</small>
                            </td>
                            <td>{{ $transaction->student->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->issuer->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->issue_date->format('M d, Y') }}</td>
                            <td>
                                {{ $transaction->due_date->format('M d, Y') }}
                                @if($transaction->isOverdue())
                                    <br><small class="text-danger">({{ $transaction->days_overdue }} days overdue)</small>
                                @endif
                            </td>
                            <td>{{ $transaction->return_date ? $transaction->return_date->format('M d, Y') : '-' }}</td>
                            <td>
                                @if($transaction->status == 'issued')
                                    <span class="badge badge-warning">Issued</span>
                                @elseif($transaction->status == 'returned')
                                    <span class="badge badge-success">Returned</span>
                                @elseif($transaction->status == 'lost')
                                    <span class="badge badge-danger">Lost</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->fine_amount > 0)
                                    <span class="text-danger">${{ number_format($transaction->fine_amount, 2) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if($transaction->status == 'issued')
                                                <a href="{{ route('librarian.books.show', $transaction->book_id) }}" class="dropdown-item">
                                                    <i class="icon-eye"></i> View Book
                                                </a>
                                                <form action="{{ route('librarian.books.return', ['book' => $transaction->book_id, 'transaction' => $transaction->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="icon-check"></i> Return Book
                                                    </button>
                                                </form>
                                            @endif
                                            @if($transaction->fine_amount > 0 && $transaction->status != 'returned')
                                                <form action="{{ route('librarian.transactions.pay_fine', $transaction->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="icon-cash"></i> Pay Fine
                                                    </button>
                                                </form>
                                            @endif
                                            @if($transaction->status == 'issued')
                                                <form action="{{ route('librarian.transactions.lost', $transaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this book as lost? A fine of $500 will be applied.');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="icon-warning"></i> Mark as Lost
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info">
                                    <i class="icon-info"></i> No transactions found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

@endsection
