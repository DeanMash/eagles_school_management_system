@extends('layouts.master')
@section('page_title', 'Transaction Report')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Transaction Report</h6>
        <div class="header-elements">
            <a href="{{ route('librarian.transactions.index') }}" class="btn btn-secondary">
                <i class="icon-arrow-left7"></i> Back to Transactions
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Report Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h4 class="mb-0">{{ $reportData['start_date'] }}</h4>
                        <small>Start Date</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h4 class="mb-0">{{ $reportData['end_date'] }}</h4>
                        <small>End Date</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $reportData['total_issued'] }}</h3>
                        <small>Books Issued</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $reportData['total_returned'] }}</h3>
                        <small>Books Returned</small>
                    </div>
                </div>
            </div>
        </div>

        @if($reportData['total_fines'] > 0)
            <div class="alert alert-warning">
                <h5 class="mb-0">
                    <i class="icon-cash"></i> Total Fines Collected: <strong>${{ number_format($reportData['total_fines'], 2) }}</strong>
                </h5>
            </div>
        @endif

        @if($reportData['most_popular_book'])
            <div class="alert alert-info">
                <h5 class="mb-0">
                    <i class="icon-star-full2"></i> Most Popular Book: 
                    <strong>{{ $reportData['most_popular_book']->title }}</strong> 
                    by {{ $reportData['most_popular_book']->author }}
                </h5>
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
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData['transactions'] as $transaction)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $transaction->book->title ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $transaction->book->author ?? '' }}</small>
                            </td>
                            <td>{{ $transaction->student->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->issue_date->format('M d, Y') }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-info">
                                    <i class="icon-info"></i> No transactions found for the selected period.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
