@extends('layouts.master')
@section('page_title', 'Edit Book')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Edit Book: {{ $book->title }}</h6>
        <div class="header-elements">
            <a href="{{ route('librarian.books.index') }}" class="btn btn-secondary">
                <i class="icon-arrow-left7"></i> Back to Books
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('librarian.books.update', $book->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $book->isbn) }}" placeholder="ISBN Number">
                        @error('isbn')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $book->title) }}" required>
                        @error('title')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Author <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control" value="{{ old('author', $book->author) }}" required>
                        @error('author')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Publisher</label>
                        <input type="text" name="publisher" class="form-control" value="{{ old('publisher', $book->publisher) }}">
                        @error('publisher')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Year Published</label>
                        <input type="number" name="year_published" class="form-control" value="{{ old('year_published', $book->year_published) }}" min="1000" max="{{ date('Y') }}">
                        @error('year_published')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-control" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $book->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Number of Copies <span class="text-danger">*</span></label>
                        <input type="number" name="copies" class="form-control" value="{{ old('copies', $book->copies) }}" required min="1" max="1000">
                        <small class="form-text text-muted">Available: {{ $book->available_copies }}</small>
                        @error('copies')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Shelf Number</label>
                        <input type="text" name="shelf_number" class="form-control" value="{{ old('shelf_number', $book->shelf_number) }}">
                        @error('shelf_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="available" {{ old('status', $book->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="checked_out" {{ old('status', $book->status) == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                            <option value="reserved" {{ old('status', $book->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="lost" {{ old('status', $book->status) == 'lost' ? 'selected' : '' }}>Lost</option>
                            <option value="damaged" {{ old('status', $book->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Book Cover</label>
                @if($book->book_cover)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $book->book_cover) }}" alt="Current Cover" style="max-width: 150px; max-height: 200px;">
                    </div>
                @endif
                <input type="file" name="book_cover" class="form-control" accept="image/*">
                <small class="form-text text-muted">Leave empty to keep current cover. Max size: 2MB</small>
                @error('book_cover')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $book->description) }}</textarea>
                @error('description')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-check"></i> Update Book
                </button>
                <a href="{{ route('librarian.books.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
