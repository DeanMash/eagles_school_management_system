@extends('layouts.master')
@section('page_title', 'Accountant Dashboard')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="icon-cash"></i> Accountant Dashboard
                        <small class="float-right">Welcome, {{ Auth::user()->name }}!</small>
                    </h4>
                </div>
                <div class="card-body">
                    
                    <!-- Quick Stats Row -->
                    <div class="row mb-4">
                        <!-- Total Payments Card -->
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $total_payments ?? 0 }}</h3>
                                            <small>Total Payments</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-cash icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('payments.index') }}" class="text-white">
                                        <small>View All <i class="icon-arrow-right7"></i></small>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Current Year Payments -->
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $current_year_payments ?? 0 }}</h3>
                                            <small>This Year Payments</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-calendar icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small>Session: {{ Qs::getCurrentSession() }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Total Collected -->
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">${{ number_format($total_collected ?? 0, 2) }}</h3>
                                            <small>Total Collected</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-checkmark-circle icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small>This Year</small>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Amount -->
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">${{ number_format(($total_due ?? 0) - ($total_collected ?? 0), 2) }}</h3>
                                            <small>Pending Amount</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="icon-clock icon-2x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small>Outstanding</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hostel Statistics Row -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0"><i class="icon-home9"></i> Hostel Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h3 class="text-primary">{{ $total_dorms ?? 0 }}</h3>
                                            <small class="text-muted">Total Dormitories</small>
                                        </div>
                                        <div class="col-6">
                                            <h3 class="text-success">{{ $students_in_hostels ?? 0 }}</h3>
                                            <small class="text-muted">Students in Hostels</small>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('dorms.index') }}" class="btn btn-sm btn-secondary btn-block">
                                            <i class="icon-home9"></i> Manage Dormitories
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hostel Occupancy -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="icon-users"></i> Hostel Occupancy by Dormitory</h5>
                                </div>
                                <div class="card-body">
                                    @if(isset($hostel_occupancy) && $hostel_occupancy->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Dormitory Name</th>
                                                        <th>Students</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($hostel_occupancy as $occupancy)
                                                        <tr>
                                                            <td>{{ $occupancy['dorm_name'] }}</td>
                                                            <td>
                                                                <span class="badge badge-primary">{{ $occupancy['count'] }}</span>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('dorms.index') }}" class="btn btn-xs btn-info">
                                                                    <i class="icon-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="icon-info"></i> No students currently assigned to hostels.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Payments & Pending Payments -->
                    <div class="row">
                        <!-- Recent Payments -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="icon-list"></i> Recent Payments
                                        <a href="{{ route('payments.index') }}" class="float-right text-white">
                                            <small>View All <i class="icon-arrow-right7"></i></small>
                                        </a>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(isset($recent_payments) && $recent_payments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Payment</th>
                                                        <th>Amount</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recent_payments as $payment)
                                                        <tr>
                                                            <td>
                                                                <small>{{ $payment->student->name ?? 'N/A' }}</small>
                                                            </td>
                                                            <td>
                                                                <small>{{ $payment->payment->name ?? 'N/A' }}</small>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-success">
                                                                    ${{ number_format($payment->amt_paid ?? 0, 2) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small>{{ $payment->created_at ? $payment->created_at->format('M d, Y') : 'N/A' }}</small>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="icon-info"></i> No recent payments found.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Pending Payments -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0">
                                        <i class="icon-clock"></i> Pending Payments
                                        <a href="{{ route('payments.manage') }}" class="float-right text-white">
                                            <small>Manage <i class="icon-arrow-right7"></i></small>
                                        </a>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(isset($pending_payments) && $pending_payments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Payment</th>
                                                        <th>Due</th>
                                                        <th>Paid</th>
                                                        <th>Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($pending_payments as $payment)
                                                        <tr>
                                                            <td>
                                                                <small>{{ $payment->student->name ?? 'N/A' }}</small>
                                                            </td>
                                                            <td>
                                                                <small>{{ $payment->payment->name ?? 'N/A' }}</small>
                                                            </td>
                                                            <td>
                                                                <small>${{ number_format($payment->payment->amount ?? 0, 2) }}</small>
                                                            </td>
                                                            <td>
                                                                <small>${{ number_format($payment->amt_paid ?? 0, 2) }}</small>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-danger">
                                                                    ${{ number_format($payment->balance ?? 0, 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            <i class="icon-checkmark-circle"></i> No pending payments! All payments are up to date.
                                        </div>
                                    @endif
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
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-block">
                                                <i class="icon-plus-circle2"></i><br>
                                                Create Payment
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('payments.index') }}" class="btn btn-info btn-block">
                                                <i class="icon-list"></i><br>
                                                Manage Payments
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('payments.manage') }}" class="btn btn-warning btn-block">
                                                <i class="icon-users"></i><br>
                                                Student Payments
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="{{ route('dorms.index') }}" class="btn btn-secondary btn-block">
                                                <i class="icon-home9"></i><br>
                                                Hostel Management
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
