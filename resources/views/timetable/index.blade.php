@extends('layouts.master')

@section('title', 'Timetables')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Timetables Management</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h5><i class="icon-info22"></i> Timetable Feature</h5>
            The timetable management system is currently under development.
            This feature will allow you to create and manage class schedules.
        </div>
        
        <div class="text-center py-5">
            <i class="icon-calendar3 icon-2x text-muted mb-3"></i>
            <h4>Timetable Coming Soon</h4>
            <p class="text-muted">Check back later for timetable management features.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection