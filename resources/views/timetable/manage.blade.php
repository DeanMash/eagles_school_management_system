@extends('layouts.master')

@section('title', 'Manage Timetable')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Timetable</h3>
    </div>
    <div class="card-body">
        <p>Timetable ID: {{ $id }}</p>
        <p>Management feature will be available in the next update.</p>
        <a href="{{ route('tt.index') }}" class="btn btn-secondary">Back to Timetables</a>
    </div>
</div>
@endsection