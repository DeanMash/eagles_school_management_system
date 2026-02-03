@extends('layouts.master')
@section('page_title', 'Daily Payment Report - ' . $reportDate)
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold">
            <i class="icon-calendar52 mr-2"></i>Daily Payment Report
            <span class="badge badge-primary ml-2">{{ $reportDate }}</span>
        </h6>
        <div class="header-elements">
            <button onclick="window.print()" class="btn btn-sm btn-info"><i class="icon-printer mr-1"></i> Print</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p class="mb-0"><strong>Report Date:</strong> {{ \Carbon\Carbon::parse($reportDate)->format('l, F j, Y') }}</p>
            </div>
            <div class="col-md-6 text-right">
                <h4 class="text-success mb-0">Total Collected: {{ number_format($totalCollected) }}</h4>
            </div>
        </div>

        @if($receipts->count() > 0)
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Time</th>
                        <th>Student</th>
                        <th>Payment</th>
                        <th>Amount Paid</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipts as $r)
                        @php $pr = $r->pr; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $r->created_at->format('h:i A') }}</td>
                            <td>{{ $pr && $pr->student ? $pr->student->name : '—' }}</td>
                            <td>{{ $pr && $pr->payment ? $pr->payment->title : '—' }}</td>
                            <td class="font-weight-bold">{{ number_format($r->amt_paid) }}</td>
                            <td>{{ number_format($r->balance ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-muted mt-3">
                <i class="icon-info22"></i> Submit this report to admin via email or print. Ensure mail is configured in .env for email submission.
            </p>
        @else
            <div class="alert alert-info">
                <i class="icon-info22 mr-2"></i>No payments were recorded today.
            </div>
        @endif
    </div>
</div>

@endsection
