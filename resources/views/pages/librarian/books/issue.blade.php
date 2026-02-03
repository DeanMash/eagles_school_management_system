@extends('layouts.master')
@section('page_title', 'Issue Book: ' . $book->title)
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Issue Book: {{ $book->title }}</h6>
        <div class="header-elements">
            <a href="{{ route('librarian.books.index') }}" class="btn btn-secondary">
                <i class="icon-arrow-left7"></i> Back to Books
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Book Info -->
        <div class="alert alert-info">
            <strong>Book Information:</strong><br>
            <strong>Title:</strong> {{ $book->title }}<br>
            <strong>Author:</strong> {{ $book->author }}<br>
            <strong>Available Copies:</strong> {{ $book->available_copies }} / {{ $book->copies }}
        </div>

        @if(!$book->isAvailable())
            <div class="alert alert-warning">
                <i class="icon-warning"></i> This book is not available for issuing. Available copies: {{ $book->available_copies }}
            </div>
        @endif

        <form method="POST" action="{{ route('librarian.books.issue.store', $book->id) }}">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-control select-search" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} 
                                    @if($student->studentRecord)
                                        ({{ $student->studentRecord->adm_no }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        <small class="form-text text-muted">Select the date when the book should be returned</small>
                        @error('due_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary" {{ !$book->isAvailable() ? 'disabled' : '' }}>
                    <i class="icon-check"></i> Issue Book
                </button>
                <a href="{{ route('librarian.books.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
