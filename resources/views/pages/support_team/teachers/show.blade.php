@extends('layouts.master')
@section('page_title', 'Teacher Details - ' . $teacher->name)
@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $teacher->photo }}" alt="{{ $teacher->name }}" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h4>{{ $teacher->name }}</h4>
                <p class="text-muted">{{ ucfirst($teacher->user_type) }}</p>
                <p><strong>Username:</strong> <code>{{ $teacher->username }}</code></p>
                @if($teacher->email)
                    <p><strong>Email:</strong> {{ $teacher->email }}</p>
                @endif
                @if($teacher->phone)
                    <p><strong>Phone:</strong> {{ $teacher->phone }}</p>
                @endif
                @if($teacher->gender)
                    <p><strong>Gender:</strong> {{ $teacher->gender }}</p>
                @endif
                @if($teacher->address)
                    <p><strong>Address:</strong> {{ $teacher->address }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Class Teacher Assignments</h6>
            </div>
            <div class="card-body">
                @if($sections->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sections as $section)
                                <tr>
                                    <td>{{ $section->my_class->name }}</td>
                                    <td>{{ $section->name }}</td>
                                    <td>{{ \App\Models\StudentRecord::where('section_id', $section->id)->count() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">This teacher is not assigned as a class teacher for any section.</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title">Subject Assignments</h6>
            </div>
            <div class="card-body">
                @if($subjects->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Short Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->name }}</td>
                                    <td>{{ $subject->my_class->name }}</td>
                                    <td>{{ $subject->slug }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">This teacher is not assigned to teach any subjects.</p>
                @endif
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Back to Teachers</a>
            <a href="{{ route('teachers.edit', Qs::hash($teacher->id)) }}" class="btn btn-primary">Edit Teacher</a>
        </div>
    </div>
</div>

@endsection
