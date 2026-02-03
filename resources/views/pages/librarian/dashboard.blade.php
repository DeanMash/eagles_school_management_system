@extends('layouts.master')
@section('page_title', 'Librarian Dashboard')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="icon-book"></i> Librarian Dashboard
                        <small class="float-right">Welcome, {{ Auth::user()->name }}!</small>
                    </h4>
                </div>
                <div class="card-body">
                    
                    <!-- Quick Stats Row -->
                    <div class="row mb-4">
                        <!-- Total Books Card -->
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $totalBooks ?? 0 }}</h3>
                                            <small>Total Books</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-book icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('librarian.books.index') }}" class="text-white">
                                        <small>View All <i class="icon-arrow-right7"></i></small>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Available Books -->
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $availableBooks ?? 0 }}</h3>
                                            <small>Available Books</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-checkmark-circle icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('librarian.books.index') }}" class="text-white">
                                        <small>View Available <i class="icon-arrow-right7"></i></small>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Issued Books -->
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $issuedBooks ?? 0 }}</h3>
                                            <small>Issued Books</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-user-check icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('librarian.transactions.index') }}" class="text-white">
                                        <small>View Transactions <i class="icon-arrow-right7"></i></small>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Books -->
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $overdueBooks ?? 0 }}</h3>
                                            <small>Overdue Books</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-warning icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('librarian.transactions.overdue') }}" class="text-white">
                                        <small>View Overdue <i class="icon-arrow-right7"></i></small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions and Overdue Books -->
                    <div class="row">
                        <!-- Recent Transactions -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="icon-list"></i> Recent Transactions</h5>
                                </div>
                                <div class="card-body">
                                    @if($recentTransactions->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Book</th>
                                                        <th>Student</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentTransactions as $transaction)
                                                        <tr>
                                                            <td>{{ $transaction->book->title ?? 'N/A' }}</td>
                                                            <td>{{ $transaction->student->name ?? 'N/A' }}</td>
                                                            <td>
                                                                @if($transaction->status == 'issued')
                                                                    <span class="badge badge-warning">Issued</span>
                                                                @elseif($transaction->status == 'returned')
                                                                    <span class="badge badge-success">Returned</span>
                                                                @else
                                                                    <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="icon-info"></i> No recent transactions found.
                                        </div>
                                    @endif
                                    <div class="text-center mt-3">
                                        <a href="{{ route('librarian.transactions.index') }}" class="btn btn-sm btn-info">
                                            View All Transactions <i class="icon-arrow-right7"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Overdue Books -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0"><i class="icon-warning"></i> Overdue Books</h5>
                                </div>
                                <div class="card-body">
                                    @if($overdueTransactions->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Book</th>
                                                        <th>Student</th>
                                                        <th>Due Date</th>
                                                        <th>Days Overdue</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($overdueTransactions as $transaction)
                                                        @php
                                                            $daysOverdue = now()->diffInDays($transaction->due_date);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $transaction->book->title ?? 'N/A' }}</td>
                                                            <td>{{ $transaction->student->name ?? 'N/A' }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($transaction->due_date)->format('M d, Y') }}</td>
                                                            <td>
                                                                <span class="badge badge-danger">{{ $daysOverdue }} day(s)</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            <i class="icon-checkmark-circle"></i> No overdue books! Great job!
                                        </div>
                                    @endif
                                    <div class="text-center mt-3">
                                        <a href="{{ route('librarian.transactions.overdue') }}" class="btn btn-sm btn-danger">
                                            View All Overdue <i class="icon-arrow-right7"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="icon-grid"></i> Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <a href="{{ route('librarian.books.create') }}" class="btn btn-primary btn-block">
                                                <i class="icon-plus-circle2"></i> Add New Book
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('librarian.books.index') }}" class="btn btn-info btn-block">
                                                <i class="icon-book"></i> Manage Books
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('librarian.transactions.index') }}" class="btn btn-warning btn-block">
                                                <i class="icon-list"></i> View Transactions
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('librarian.transactions.overdue') }}" class="btn btn-danger btn-block">
                                                <i class="icon-warning"></i> Overdue Books
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
