@extends('layouts.master')
@section('page_title', 'Overdue Books')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline bg-danger text-white">
        <h6 class="card-title">
            <i class="icon-warning"></i> Overdue Books
        </h6>
        <div class="header-elements">
            <a href="{{ route('librarian.transactions.index') }}" class="btn btn-light">
                <i class="icon-arrow-left7"></i> Back to Transactions
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Total Fines Summary -->
        <div class="alert alert-danger">
            <h5 class="mb-0">
                <i class="icon-warning"></i> Total Overdue Books: {{ $transactions->count() }}
                @if($totalFines > 0)
                    | Total Fines: <strong>${{ number_format($totalFines, 2) }}</strong>
                @endif
            </h5>
        </div>

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

        <!-- Overdue Books Table -->
        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Book</th>
                            <th>Student</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Fine Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr class="{{ $transaction->days_overdue > 30 ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $transaction->book->title ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $transaction->book->author ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $transaction->student->name ?? 'N/A' }}<br>
                                    @if($transaction->student && $transaction->student->studentRecord)
                                        <small class="text-muted">{{ $transaction->student->studentRecord->adm_no }}</small>
                                    @endif
                                </td>
                                <td>{{ $transaction->issue_date->format('M d, Y') }}</td>
                                <td>
                                    <strong class="text-danger">{{ $transaction->due_date->format('M d, Y') }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-danger">{{ $transaction->days_overdue }} day(s)</span>
                                </td>
                                <td>
                                    <strong class="text-danger">${{ number_format($transaction->calculateFine(), 2) }}</strong>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('librarian.books.show', $transaction->book_id) }}" class="btn btn-sm btn-info">
                                            <i class="icon-eye"></i> View
                                        </a>
                                        <form action="{{ route('librarian.books.return', ['book' => $transaction->book_id, 'transaction' => $transaction->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="icon-check"></i> Return
                                            </button>
                                        </form>
                                        @if($transaction->fine_amount > 0)
                                            <form action="{{ route('librarian.transactions.pay_fine', $transaction->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="icon-cash"></i> Pay Fine
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-success">
                <i class="icon-checkmark-circle"></i> Great! No overdue books at the moment.
            </div>
        @endif
    </div>
</div>

@endsection
