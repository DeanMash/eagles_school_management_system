@extends('layouts.master')
@section('page_title', 'Book Details: ' . $book->title)
@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($book->book_cover)
                    <img src="{{ asset('storage/' . $book->book_cover) }}" alt="Book Cover" class="img-fluid mb-3" style="max-height: 400px;">
                @else
                    <div class="bg-secondary text-white p-5 mb-3">
                        <i class="icon-book icon-3x"></i>
                    </div>
                @endif
                
                <h4>{{ $book->title }}</h4>
                <p class="text-muted">by {{ $book->author }}</p>
                
                <div class="mt-3">
                    @if($book->isAvailable())
                        <a href="{{ route('librarian.books.issue', $book->id) }}" class="btn btn-primary btn-block">
                            <i class="icon-user-check"></i> Issue Book
                        </a>
                    @else
                        <button class="btn btn-secondary btn-block" disabled>
                            Not Available
                        </button>
                    @endif
                    
                    <a href="{{ route('librarian.books.edit', $book->id) }}" class="btn btn-info btn-block mt-2">
                        <i class="icon-pencil"></i> Edit Book
                    </a>
                    
                    <a href="{{ route('librarian.books.index') }}" class="btn btn-secondary btn-block mt-2">
                        <i class="icon-arrow-left7"></i> Back to Books
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Book Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">ISBN</th>
                        <td>{{ $book->isbn ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Title</th>
                        <td><strong>{{ $book->title }}</strong></td>
                    </tr>
                    <tr>
                        <th>Author</th>
                        <td>{{ $book->author }}</td>
                    </tr>
                    <tr>
                        <th>Publisher</th>
                        <td>{{ $book->publisher ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Year Published</th>
                        <td>{{ $book->year_published ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><span class="badge badge-info">{{ $book->category }}</span></td>
                    </tr>
                    <tr>
                        <th>Shelf Number</th>
                        <td>{{ $book->shelf_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Total Copies</th>
                        <td>{{ $book->copies }}</td>
                    </tr>
                    <tr>
                        <th>Available Copies</th>
                        <td>
                            <span class="badge {{ $book->available_copies > 0 ? 'badge-success' : 'badge-danger' }}">
                                {{ $book->available_copies }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($book->status == 'available')
                                <span class="badge badge-success">Available</span>
                            @elseif($book->status == 'checked_out')
                                <span class="badge badge-warning">Checked Out</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($book->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @if($book->description)
                    <tr>
                        <th>Description</th>
                        <td>{{ $book->description }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title">Transaction History</h6>
            </div>
            <div class="card-body">
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Issued By</th>
                                    <th>Due Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th>Fine</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->issue_date->format('M d, Y') }}</td>
                                        <td>{{ $transaction->student->name ?? 'N/A' }}</td>
                                        <td>{{ $transaction->issuer->name ?? 'N/A' }}</td>
                                        <td>{{ $transaction->due_date->format('M d, Y') }}</td>
                                        <td>{{ $transaction->return_date ? $transaction->return_date->format('M d, Y') : '-' }}</td>
                                        <td>
                                            @if($transaction->status == 'issued')
                                                <span class="badge badge-warning">Issued</span>
                                            @elseif($transaction->status == 'returned')
                                                <span class="badge badge-success">Returned</span>
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
                                            @if($transaction->status == 'issued')
                                                <form action="{{ route('librarian.books.return', ['book' => $book->id, 'transaction' => $transaction->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="icon-check"></i> Return
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="icon-info"></i> No transactions found for this book.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
